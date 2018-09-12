<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Acm\Model\ResourceModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Item extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	protected $_filesystem;

    /**
     * File Uploader factory
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;
	
	/**
     * @var \Magento\Framework\Stdlib\DateTime
     */
	protected $_date;
	
	/**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        parent::__construct($context);
		$this->_date = $date;
        $this->_filesystem = $filesystem;
		$this->_objectManager = $objectManager;
        $this->_fileUploaderFactory = $fileUploaderFactory;
    }
	
    /**
     * Initialize connection and table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mgs_acm_item', 'item_id');
    }
	
	public function getModel($type){
		return $this->_objectManager->create($type);
	}
	
	protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
		$objectId = $object->getId();
        $oldStores = $this->lookupStoreIds($objectId);
        $newStores = (array)$object->getStores();
		
		$table = $this->getTable('mgs_acm_item_store');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);
		
		$connection = $this->getConnection();

		// Delete old store view of item
        if ($delete) {
            $where = ['item_id = ?' => (int)$object->getId(), 'store_id IN (?)' => $delete];

            $connection->delete($table, $where);
        }
		
		// Assign new store view to item
        if ($insert) {
            $data = [];

            foreach ($insert as $storeId) {
                $data[] = ['item_id' => (int)$object->getId(), 'store_id' => (int)$storeId];
            }
            $connection->insertMultiple($table, $data);
        }
		
		// Assign Products to item
		if($object->getProductIds()){
			$where = ['item_id = ?' => (int)$object->getId()];
            $connection->delete($this->getTable('mgs_acm_item_products'), $where);
			
			$productData = [];
			$productIds = (array)$object->getProductIds();
			if(count($productIds)>0){
				foreach($productIds as $productId => $pos){
					$position = $pos['position'];
					if($position==''){
						$position = 0;
					}
					$productData[] = ['item_id' => (int)$object->getId(), 'product_id' => $productId, 'position' => $position];
				}
				
				$connection->insertMultiple($this->getTable('mgs_acm_item_products'), $productData);
			}
		}

        $select = $connection->select()->from(
            $this->getTable('mgs_acm_item_value'),
            'value_id'
        )->where(
            'item_id = :item_id'
        );
		
		$binds = [':item_id' => (int)$object->getId()];
		
		$valueIds = $connection->fetchCol($select, $binds);
		
		// Delete from table mgs_acm_value
		$connection->delete($this->getTable('mgs_acm_value'), ['value_id IN (?)' => $valueIds]);
		
		// Delete from table mgs_acm_item_value
		$connection->delete($this->getTable('mgs_acm_item_value'), ['item_id = ?' => (int)$object->getId()]);
		
		$arrItemValue = (array)$object->getItem();
		$arrValue = [];
		$arrData = [];
		foreach($arrItemValue as $identifier=>$value){
			//if($value){
				$select = $connection->select()->from(
					$this->getTable('mgs_acm_field'),
					['field_id', 'type']
				)->where(
					'acm_type_id = :acm_type_id'
				)->where(
					'identifier = "'.$identifier.'"'
				);
				$binds = [':acm_type_id' => (int)$object->getAcmTypeId()];
				
				$field = $connection->fetchRow($select, $binds);
				if(is_array($value)){
					$value = implode(',',$value);
				}
				if($field['type'] == 'date'){
					$value = date('Y-m-d',strtotime($value));
				}
				$model = $this->_objectManager->create('MGS\Acm\Model\Value')->setAcmFieldId($field['field_id'])->setValue($value)->save();
				$arrValue[] = ['item_id' => (int)$object->getId(), 'value_id' => (int)$model->getId()];
				$arrData[$identifier] = $value;
			//}
		}
		
		$files = $_FILES;
		$arrUpload = [];
		if(count($files)>0){
			foreach($files as $identifier => $file){
				if($file['name']!=''){
					$select = $connection->select()->from(
						$this->getTable('mgs_acm_field'),
						['field_id', 'type', 'additional_content']
					)->where(
						'acm_type_id = :acm_type_id'
					)->where(
						'identifier = "'.$identifier.'"'
					);
					$binds = [':acm_type_id' => (int)$object->getAcmTypeId()];
					
					$field = $connection->fetchRow($select, $binds);
					
					if($field['type']=='file' || $field['type']=='image'){
						$addPath = '';
						try {
							if($field['additional_content'] != ''){
								$additionalInfo = json_decode($field['additional_content'],true);
								$extensions = explode(',',$additionalInfo['ex']);
								$addPath = $additionalInfo['path'];
								$lastChar = substr($addPath, -1);
								if($lastChar == '/'){
									$addPath = substr($addPath, 0, -1);
								}
							}
							$uploader = $this->_fileUploaderFactory->create(['fileId' => $identifier]);
							$uploader->setAllowedExtensions($extensions);
							$uploader->setAllowRenameFiles(true);
							$uploader->setFilesDispersion(true);
							
						} catch (\Exception $e) {
							return $this;
						}
						$path = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($addPath);
						$uploader->save($path);
						$value = $uploader->getUploadedFileName();
						if($addPath!=''){
							$value = $addPath.$value;
						}
						
						$model = $this->_objectManager->create('MGS\Acm\Model\Value')->setAcmFieldId($field['field_id'])->setValue($value)->save();
						$arrValue[] = ['item_id' => (int)$object->getId(), 'value_id' => (int)$model->getId()];
						$arrData[$identifier] = $value;
						$arrUpload[] = $identifier;
					}
				}
			}
		}
		$arrData['acm_upload_file'] = $arrUpload;
		
		if(count($arrValue)>0){
			$connection->insertMultiple($this->getTable('mgs_acm_item_value'), $arrValue);
		}
		
		$object->setData($arrData);
		$object->setId($objectId);
		
		return parent::_afterSave($object);
    }
	
	public function lookupStoreIds($id)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getTable('mgs_acm_item_store'),
            'store_id'
        )->where(
            'item_id = :item_id'
        );

        $binds = [':item_id' => (int)$id];

        return $connection->fetchCol($select, $binds);
    }
	
	/**
     * Perform operations after object load
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {	
        if ($object->getId()) {
			$item = $this->getModel('MGS\Acm\Model\Item')
				->getCollection()
				->addTypeToFilter($object->getAcmTypeId())
				->addFieldToFilter('main_table.item_id', $object->getId());
			//echo $item->getSelect();
			foreach($item as $_item){
				//$object = $this->setDataByItem($_item, $object);
				if($object->getId() == $_item->getId()){
					$object = $this->setDataByItem($_item, $object);
				}
			}
			
			$stores = $this->lookupStoreIds($object->getId());
			$object->setData('store_id', $stores);
			$object->setData('stores', $stores);
        }
		
		

        return parent::_afterLoad($object);
    }
	
	/**
     * Process block data before deleting
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     */
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
		$connection = $this->getConnection();
		
        $condition = ['item_id = ?' => (int)$object->getId()];
		
		$select = $connection->select()->from(
            $this->getTable('mgs_acm_item_value'),
            'value_id'
        )->where(
            'item_id = :item_id'
        );
		
		$binds = [':item_id' => (int)$object->getId()];
		
		$valueIds = $connection->fetchCol($select, $binds);
		$connection->delete($this->getTable('mgs_acm_value'), ['value_id IN (?)' => $valueIds]);
        $connection->delete($this->getTable('mgs_acm_item_store'), $condition);
        $connection->delete($this->getTable('mgs_acm_item_value'), $condition);
		

        return parent::_beforeDelete($object);
    }
	
	public function setDataByItem($item, $object){
		$fields = $this->getModel('MGS\Acm\Model\Field')
			->getCollection()
			->addFieldToFilter('acm_type_id', $item->getAcmTypeId());
		foreach($fields as $field){
			if($item->getData($field->getIdentifier()) != ''){
				$object->setData($field->getIdentifier(), $this->setDataType($field->getType(), $item->getData($field->getIdentifier())));
			}
		}
		return $object;
	}
	
	public function setDataType($type, $value){
		$result = '';
		switch($type){
			case 'checkboxes':
				$result = explode(',',$value);
				break;
			case 'multiselect':
				$result = explode(',',$value);
				break;
			default:
				$result = $value;
				break;
		}
		return $result;
	}
}
