<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Acm\Model\ResourceModel\Field;

/**
 * Acm resource model collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Init resource collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('MGS\Acm\Model\Field', 'MGS\Acm\Model\ResourceModel\Field');
    }
	
	public function getShortInfo($typeId){
		$this->addFieldToSelect('identifier')
			->addFieldToSelect('type')
			->addFieldToFilter('acm_type_id', $typeId);
		$sql = $this->getSelect();
		return $this->getConnection()->fetchAll($sql);
	}
}
