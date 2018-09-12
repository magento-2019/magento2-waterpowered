<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml review main block
 */
namespace MGS\Acm\Block\Adminhtml\Item;

class Main extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize add new review
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_addButtonLabel = __('New Item');
        if($model = $this->_coreRegistry->registry('acm_item')){
			if($model->getContentType()==1){
				parent::_construct();
			}
		}

        $this->_blockGroup = 'MGS_Acm';
        $this->_controller = 'adminhtml';
    }
	
	/**
     * @return string
     */
    public function getCreateUrl()
    {
        return $this->getUrl('*/*/newitem', ['type_id'=>$this->getRequest()->getParam('type_id')]);
    }
}
