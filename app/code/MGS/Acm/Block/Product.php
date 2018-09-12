<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Acm\Block;

/**
 * Main contact form block
 */
class Product extends \Magento\Catalog\Block\Product\AbstractProduct
{	
	/**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
	
	protected $_count;
    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
		\Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
		$this->_objectManager = $objectManager;
        parent::__construct($context, $data);
    }
	
	public function getModel($model){
		return $this->_objectManager->create($model);
	}
	
	/**
     * Product collection initialize process
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    public function getProductCollection()
    {
		$result = [];
        $acmProductCollection = $this->getModel('MGS\Acm\Model\Product')
			->getCollection()
			->addFieldToFilter('item_id', $this->getItem()->getId())
			->setOrder('position', 'ASC');

		if(count($acmProductCollection)>0){
			foreach($acmProductCollection as $item){
				$product = $this->getModel('Magento\Catalog\Model\Product')->load($item->getProductId());
				if(($product->getStatus() == 1) && (($product->getVisibility() == 2) || ($product->getVisibility() == 4))){
					$result[] = $product;
				}
			}
		}
		return $result;
    }
	
	public function getItem(){
		return $this->getData('item');
	}
}

