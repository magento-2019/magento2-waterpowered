<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Acm\Controller\Adminhtml\Acm;

use Magento\Backend\App\Action;

class Item extends \MGS\Acm\Controller\Adminhtml\Item
{
	
	/**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
	
	public function __construct(Action\Context $context, \Magento\Framework\Registry $registry)
    {
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }
	
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
		$id = $this->getRequest()->getParam('type_id');
		$model = $this->_objectManager->create('MGS\Acm\Model\Acm')->load($id);
		$this->_coreRegistry->register('acm_item', $model);
		$resultPage = $this->_view->getPage();
        $resultPage->getConfig()->getTitle()->prepend(__('Manage %1', $model->getTitle()));
		$resultPage->addContent($resultPage->getLayout()->createBlock('MGS\Acm\Block\Adminhtml\Item\Main'));
        $this->_view->renderLayout();
    }
}
