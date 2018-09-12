<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\Acm\Block\Adminhtml;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
	
	protected $_itemFactory;
	
	protected $_massactionBlockName = 'MGS\Acm\Block\Adminhtml\Widget\Grid\Massaction\Extended';
	
	/**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \MGS\Acm\Model\ResourceModel\Item\CollectionFactory $itemFactory,
        \Magento\Framework\Registry $coreRegistry,
		\Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->_itemFactory = $itemFactory;
        $this->_coreRegistry = $coreRegistry;
		$this->_objectManager = $objectManager;
        parent::__construct($context, $backendHelper, $data);
    }
	
	public function getModel($type){
		return $this->_objectManager->create($type);
	}

    /**
     * Initialize grid
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('itemGrid');
        $this->setDefaultSort('item_id', 'DESC');
    }
	
	protected function _prepareCollection()
    {
		$typeId = $this->getRequest()->getParam('type_id');
        $collection = $this->_itemFactory->create();
		$collection->addTypeToFilter($typeId);
		
		$fields = $this->getModel('MGS\Acm\Model\Field')
			->getCollection()
			->addFieldToFilter('acm_type_id', $typeId);
		
		$arrColumns = [];
		
		if(count($fields)>0){
			foreach($fields as $_field){
				$arrColumns[] = $_field->getIdentifier();
			}
		}

		$columnId = $this->getParam($this->getVarNameSort(), $this->_defaultSort);
		$dir = $this->getParam($this->getVarNameDir(), $this->_defaultDir);
		$filter = $this->getParam($this->getVarNameFilter(), null);
		if (is_string($filter)) {
			$data = $this->_backendHelper->prepareFilterString($filter);
			$data = array_merge($data, (array)$this->getRequest()->getPost($this->getVarNameFilter()));
			//print_r($data); die();
			if(count($data)>0){
				foreach($data as $column=>$value){
					if($column != 'item_id'){
						if(in_array($column, $arrColumns)){
							if($column!='store_id'){
								if(is_array($value)){
									if(isset($value['from']) || isset($value['to'])){
										if(isset($value['from'])){
											$fromDate = date('Y-m-d',strtotime($value['from']));
										}else{
											$fromDate = '1970-01-01';
										}
										
										if(isset($value['to'])){
											$toDate = date('Y-m-d',strtotime($value['to']));
										}else{
											$toDate = date('Y-m-d');
										}
										
										$collection->getSelect()->having($column . " BETWEEN '".$fromDate."' AND '".$toDate."'");
									}
								}else{
									$collection->getSelect()->having($column.' LIKE "%'.$value.'%"');
								}
							}elseif($value){
								$collection->addStoreFilter($value);
							}
						}
					}else{
						$collection->addFieldToFilter('item_id', $value);
					}
				}
			}
		}
		$this->setCollection($collection);
		
		
		//echo $collection->getSelect();
        return parent::_prepareCollection();
		
    }
	
	/**
     * Filter store condition
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @param \Magento\Framework\DataObject $column
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _filterStoreCondition($collection, \Magento\Framework\DataObject $column)
    {
        if (!($value = $column->getFilter()->getValue())) {
            return;
        }

        $this->getCollection()->addStoreFilter($value);
    }
	
	public function getSize()
    {
        if ($this->_totalRecords === null) {
            $sql = $this->getSelectCountSql();
            $this->_totalRecords = $this->getConnection()->fetchOne($sql, $this->_bindParams);
        }
        return intval($this->_totalRecords);
    }
	
	/**
     * Apply pagination to collection
     *
     * @return void
     */
    /* protected function _preparePage()
    {
        $this->getCollection()->setPageSize((int)$this->getParam($this->getVarNameLimit(), 20));
        $this->getCollection()->setCurPage((int)$this->getParam($this->getVarNamePage(), 1));
    } */
	
	protected function _addColumnFilterToCollection($column)
    {
        return $this;
    }
	
	protected function _prepareColumns()
    {
		$typeId = $this->getRequest()->getParam('type_id');
        $fields = $this->getModel('MGS\Acm\Model\Field')
			->getCollection()
			->addFieldToFilter('acm_type_id', $typeId)
			->addFieldToFilter('in_grid', 1)
			->setOrder('position', 'ASC');
			
        $this->addColumn(
            'item_id',
            [
                'header' => __('ID'),
                'index' => 'item_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
		
		if(count($fields)>0){
			foreach($fields as $_field){
				$this->rendererColumns($this, $_field);
			}
		}
		
		
        
        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('Edit'),
                        'url' => [
                            'base' => '*/*/edititem',
                            'params' => ['type_id' => $this->getRequest()->getParam('type_id')]
                        ],
                        'field' => 'id'
                    ]
                ],
                'sortable' => false,
                'filter' => false,
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
            ]
        );

        return parent::_prepareColumns();
    }
	
	public function rendererColumns($class, $_field){
		$type = $_field->getType();
		switch($type){
			case 'text':
			case 'textarea':
				$class->addColumn(
					$_field->getIdentifier(),
					[
						'header' => $_field->getTitle(),
						'index' => $_field->getIdentifier(),
					]
				);
				break;
			case 'store':
				if (!$this->_storeManager->isSingleStoreMode()) {
					$class->addColumn(
						'store_id',
						[
							'header' => $_field->getTitle(),
							'index' => 'store_id',
							'type' => 'store',
							'store_all' => true,
							'store_view' => true,
							'sortable' => false,
							'filter_condition_callback' => [$this, '_filterStoreCondition']
						]
					);
				}
				break;
			case 'date':
				$class->addColumn(
					$_field->getIdentifier(),
					[
						'header' => $_field->getTitle(),
						'index' => $_field->getIdentifier(),
						'type'	=> 'date',
						'renderer' => '\MGS\Acm\Block\Adminhtml\Grid\Renderer\Date'
					]
				);
				break;
			case 'file':
				$class->addColumn(
					$_field->getIdentifier(),
					[
						'header' => $_field->getTitle(),
						'index' => $_field->getIdentifier(),
						'renderer' => '\MGS\Acm\Block\Adminhtml\Grid\Renderer\File'
					]
				);
				break;
			case 'image':
				$class->addColumn(
					$_field->getIdentifier(),
					[
						'header' => $_field->getTitle(),
						'index' => $_field->getIdentifier(),
						'renderer' => '\MGS\Acm\Block\Adminhtml\Grid\Renderer\Image',
						'filter'	=> false,
						'sortable'	=> false
					]
				);
				break;
			case 'products':
				break;
			default:
				$options = [''=>__('All')];
				if($_field->getAdditionalContent()!=''){
					$additionalInfo = json_decode($_field->getAdditionalContent(),true);
					if(isset($additionalInfo['option']) && (count($additionalInfo['option'])>0)){
						usort($additionalInfo['option'], function($a, $b) {
							return $a['position'] - $b['position'];
						});
						foreach($additionalInfo['option'] as $key => $_option){
							$value = $_option['value'];
							if($value==''){
								$value = $_option['title'];
							}
							$options[$value] = $_option['title'];
						}
					}
				}
				if($_field->getType()=='multiselect' || $_field->getType()=='checkboxes'){
					$class->addColumn(
						$_field->getIdentifier(),
						[
							'header' => $_field->getTitle(),
							'index' => $_field->getIdentifier(),
							'type'	=> 'options',
							'options' => $options,
							'renderer' => '\MGS\Acm\Block\Adminhtml\Grid\Renderer\Multiselect',
							'sortable'	=> false
						]
					);
				}
				else{
					$class->addColumn(
						$_field->getIdentifier(),
						[
							'header' => $_field->getTitle(),
							'index' => $_field->getIdentifier(),
							'type'	=> 'options',
							'options' => $options
						]
					);
				}
				break;
		}
		return $class;
	}
	
	public function getMassactionBlock()
    {
        return $this->getChildBlock('massaction');
    }
	
	public function getMassactionBlockName()
    {
        return $this->_massactionBlockName;
    }
	
	/**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('item_id');
        $this->getMassactionBlock()->setFormFieldName('item');
        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('*/*/massdeleteitem',['type_id'=>$this->getRequest()->getParam('type_id')]),
                'confirm' => __('Are you sure?')
            ]
        );

        return $this;
    }
	
	/**
     * Return row url for js event handlers
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $item
     * @return string
     */
    public function getRowUrl($item)
    {
		return $this->getUrl('*/*/edititem', ['id' => $item->getId(), 'type_id'=>$this->getRequest()->getParam('type_id')]);
    }
}
