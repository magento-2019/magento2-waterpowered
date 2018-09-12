<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace MGS\Acm\Controller\Index;

class View extends \Magento\Framework\App\Action\Action
{
    protected $_storeManager;
	protected $_coreRegistry = null;
	
	/**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
	
	/**
     * @var \MGS\Acm\Model\ResourceModel\Item\CollectionFactory
     */
	protected $_itemFactory;
	
	public function __construct(
		\Magento\Framework\App\Action\Context $context, 
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\MGS\Acm\Model\ResourceModel\Item\CollectionFactory $itemFactory,
		\Magento\Framework\Registry $coreRegistry)
    {
		$this->_coreRegistry = $coreRegistry;
		$this->_storeManager = $storeManager;
		$this->resultPageFactory = $resultPageFactory;
		$this->_itemFactory = $itemFactory;
        parent::__construct($context);
		
    }
	
	public function redirectToHome(){
		$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
		$resultRedirect->setUrl($this->_storeManager->getStore()->getBaseUrl());
		return $resultRedirect;
	}
	
    public function execute()
    {
        if(($typeId = $this->_request->getParam('type_id')) && ($itemId = $this->_request->getParam('item_id'))){
			$model = $this->_itemFactory->create();
			$model->loadByTypeAndId($typeId, $itemId);
			$item = $model->getFirstItem();
			$this->_coreRegistry->register('current_item', $item);
			
			$contentType = $this->_objectManager->create('MGS\Acm\Model\Acm')->load($typeId);
			$this->_coreRegistry->register('current_content', $contentType);

			$resultPageFactory = new \Magento\Framework\View\Result\PageFactory($this->_objectManager);
			$resultPage = $resultPageFactory->create();
			$this->_objectManager->get('MGS\Acm\Helper\Page')->prepareResultPage($resultPage, $item);
			$resultPage->initLayout();
			return $resultPage;
		}else{
			$this->redirectToHome();
		}
    }
}
