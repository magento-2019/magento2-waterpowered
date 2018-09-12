<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Acm\Model\ResourceModel\Item;

/**
 * Acm resource model collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	/**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->storeManager = $storeManager;
    }
	
    /**
     * Init resource collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('MGS\Acm\Model\Item', 'MGS\Acm\Model\ResourceModel\Item');
		$this->_map['fields']['item_id'] = 'main_table.item_id';
        $this->_map['fields']['store'] = 'store_table.store_id';
    }
	
	public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }
        return $this;
    }
	
	protected function performAddStoreFilter($store, $withAdmin = true)
    {
        if ($store instanceof \Magento\Store\Model\Store) {
            $store = [$store->getId()];
        }

        if (!is_array($store)) {
            $store = [$store];
        }

        if ($withAdmin) {
            $store[] = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        }

        $this->addFilter('store', ['in' => $store], 'public');
    }
	
	/**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $this->performAfterLoad('mgs_acm_item_store', 'item_id');
        $this->_previewFlag = false;

        return parent::_afterLoad();
    }

	
	protected function performAfterLoad($tableName, $columnName)
    {
        $items = $this->getColumnValues($columnName);
        if (count($items)) {
            $connection = $this->getConnection();
            $select = $connection->select()->from(['item_entity_store' => $this->getTable($tableName)])
                ->where('item_entity_store.' . $columnName . ' IN (?)', $items);
            $result = $connection->fetchPairs($select);
            if ($result) {
                foreach ($this as $item) {
                    $entityId = $item->getData($columnName);
                    if (!isset($result[$entityId])) {
                        continue;
                    }
                    if ($result[$entityId] == 0) {
                        $stores = $this->storeManager->getStores(false, true);
                        $storeId = current($stores)->getId();
                        $storeCode = key($stores);
                    } else {
                        $storeId = $result[$item->getData($columnName)];
                        $storeCode = $this->storeManager->getStore($storeId)->getCode();
                    }
                    $item->setData('_first_store_id', $storeId);
                    $item->setData('store_code', $storeCode);
                    $item->setData('store_id', [$result[$entityId]]);
                }
            }
        }
    }
	
	protected function joinStoreRelationTable($tableName, $columnName)
    {
        if ($this->getFilter('store')) {
            $this->getSelect()->join(
                ['store_table' => $this->getTable($tableName)],
                'main_table.' . $columnName . ' = store_table.' . $columnName,
                []
            )->group(
                'main_table.' . $columnName
            );
        }
        parent::_renderFiltersBefore();
    }
	
	protected function _renderFiltersBefore()
    {
        $this->joinStoreRelationTable('mgs_acm_item_store', 'item_id');
    }
	
	public function addTypeToFilter($typeId){
		$connection = $this->getConnection();
		
		$select = $connection->select()
            ->from($this->getTable('mgs_acm_field'), 'identifier')
			->where('acm_type_id = '.$typeId);
		$columns = [];
		foreach ($connection->query($select)->fetchAll() as $row) {
			$columns[] = 'MAX( IF(a.identifier = "'.$row['identifier'].'", av.value, NULL) ) as '.$row['identifier'];
        }
		
		$this->getSelect()->joinLeft(array('ea' => $this->getTable('mgs_acm_item_value')),'main_table.item_id = ea.item_id', array())
			->joinLeft(array('av' => $this->getTable('mgs_acm_value')),'ea.value_id = av.value_id', array())
			->joinLeft(array('a' => $this->getTable('mgs_acm_field')),'av.acm_field_id = a.field_id', $columns)
			->group('main_table.item_id')
			->where('main_table.acm_type_id = '.$typeId);
		
		//$this->_totalRecords = count($this);
		return $this;
	}
	
	public function addFrontendFilter($typeId, $filter, $storeId = NULL){
		$connection = $this->getConnection();
		
		$select = $connection->select()
            ->from($this->getTable('mgs_acm_field'), 'identifier')
			->where('acm_type_id = '.$typeId);
		$columns = [];
		foreach ($connection->query($select)->fetchAll() as $row) {
			$columns[] = 'MAX( IF(a.identifier = "'.$row['identifier'].'", av.value, NULL) ) as '.$row['identifier'];
        }
		
		$this->getSelect()->joinLeft(array('ea' => $this->getTable('mgs_acm_item_value')),'main_table.item_id = ea.item_id', array())
			->joinLeft(array('av' => $this->getTable('mgs_acm_value')),'ea.value_id = av.value_id', array())
			->joinLeft(array('a' => $this->getTable('mgs_acm_field')),'av.acm_field_id = a.field_id', $columns)
			->group('main_table.item_id')
			->where('main_table.acm_type_id = '.$typeId)
			->order('main_table.item_id DESC');
		
		if($filter!=''){
			foreach($filter as $column => $value){
				$this->getSelect()->having($column.' LIKE "%'.$value.'%"');
			}
		}
		
		if($storeId != NULL){
			$stores = [0, $storeId];
			$storeTable = $this->getTable('mgs_acm_item_store');
			$this->getSelect()->join(
				array('stores' => $storeTable),
				'main_table.item_id = stores.item_id',
				array()
			)
			->where('stores.store_id in (?)', $stores)
			->group('main_table.item_id');
		}
		//$this->_totalRecords = count($this);
		return $this;
	}
	
	public function loadByTypeAndId($typeId, $itemId){
		$connection = $this->getConnection();
		
		$select = $connection->select()
            ->from($this->getTable('mgs_acm_field'), 'identifier')
			->where('acm_type_id = '.$typeId);
		$columns = [];
		foreach ($connection->query($select)->fetchAll() as $row) {
			$columns[] = 'MAX( IF(a.identifier = "'.$row['identifier'].'", av.value, NULL) ) as '.$row['identifier'];
        }
		
		$this->getSelect()->joinLeft(array('ea' => $this->getTable('mgs_acm_item_value')),'main_table.item_id = ea.item_id', array())
			->joinLeft(array('av' => $this->getTable('mgs_acm_value')),'ea.value_id = av.value_id', array())
			->joinLeft(array('a' => $this->getTable('mgs_acm_field')),'av.acm_field_id = a.field_id', $columns)
			->group('main_table.item_id')
			->where('main_table.acm_type_id = '.$typeId)
			->where('main_table.item_id = '.$itemId)
			->order('main_table.item_id DESC');
		
		//$this->_totalRecords = count($this);
		return $this;
	}
	
	public function getSize()
    {
        if ($this->_totalRecords === null) {
            $sql = $this->getSelect();
            $this->_totalRecords = count($this->getConnection()->fetchAll($sql));
        }
        return intval($this->_totalRecords);
    }
	
	public function addStoreToFilter($storeId) {
		$stores = [0, $storeId];
		$storeTable = $this->getTable('mgs_acm_item_store');
        $this->getSelect()->join(
                        array('stores' => $storeTable),
                        'main_table.item_id = stores.item_id',
                        array()
                )
                ->where('stores.store_id in (?)', $stores)
				->group('main_table.item_id');
		//$this->_totalRecords = count($this);
        return $this;
    }
	
	public function addFilterSelect($filter){
		foreach($filter as $column => $value){
			$this->getSelect()->having($column.' LIKE "%'.$value.'%"');
		}
		//$this->_totalRecords = count($this);
		return $this;
	}
	
	protected function _beforeLoad()
    {
        parent::_beforeLoad();
        $this->_eventManager->dispatch('core_collection_abstract_load_before', ['collection' => $this]);
        if ($this->_eventPrefix && $this->_eventObject) {
            $this->_eventManager->dispatch($this->_eventPrefix . '_load_before', [$this->_eventObject => $this]);
        }
		
		//echo $this->getSelect();
        return $this;
    }
	
	/* public function setPageSize($pageSize){
		return $this->getSelect()->limit($pageSize);
	} */
}
