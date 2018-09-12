<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Acm\Block\Adminhtml\Widget\Grid\Massaction;

/**
 * Grid widget massaction block
 *
 * @method \Magento\Quote\Model\Quote setHideFormElement(boolean $value) Hide Form element to prevent IE errors
 * @method boolean getHideFormElement()
 * @author      Magento Core Team <core@magentocommerce.com>
 * @TODO MAGETWO-31510: Remove deprecated class
 */
class Extended extends \Magento\Backend\Block\Widget\Grid\Massaction\Extended
{
	/**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;
	
	/**
     * Backend data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $_backendData = null;
	
	protected $_itemFactory;
	
	public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Helper\Data $backendData,
		\MGS\Acm\Model\ResourceModel\Item\CollectionFactory $itemFactory,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_backendData = $backendData;
		$this->_itemFactory = $itemFactory;
        parent::__construct($context, $jsonEncoder, $backendData, $data);
    }
	
    /**
     * @return string
     */
    public function getGridIdsJson()
    {
		$collection = $this->_itemFactory->create();
		$collection->addTypeToFilter($this->getRequest()->getParam('type_id'));
		$arrIds = [];
		if(count($collection)>0){
			foreach($collection as $item){
				$arrIds[] = $item->getId();
			}
		}
        return join(",", $arrIds);
    }
}
