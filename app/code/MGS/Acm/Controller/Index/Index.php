<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace MGS\Acm\Controller\Index;

use Magento\Framework\Controller\ResultFactory;
class Index extends \Magento\Framework\App\Action\Action
{
	protected $_storeManager;
	protected $_coreRegistry = null;
	
	/**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
	
	public function __construct(
		\Magento\Framework\App\Action\Context $context, 
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Framework\Registry $coreRegistry)
    {
		$this->_coreRegistry = $coreRegistry;
		$this->_storeManager = $storeManager;
		$this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
		
    }
	
	public function redirectToHome(){
		$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
		$resultRedirect->setUrl($this->_storeManager->getStore()->getBaseUrl());
		return $resultRedirect;
	}
	
    public function execute()
    {
		if($typeId = $this->_request->getParam('type_id')){
			$model = $this->_objectManager->create('MGS\Acm\Model\Acm')->load($typeId);
			if($model->getStatus()==1){
				$this->_coreRegistry->register('acm_acm', $model);

				$resultPageFactory = new \Magento\Framework\View\Result\PageFactory($this->_objectManager);
				$resultPage = $resultPageFactory->create();
				$this->_objectManager->get('MGS\Acm\Helper\Page')->prepareResultPage($resultPage, $model);
				$resultPage->initLayout();

				return $resultPage;
			}else{
				$this->redirectToHome();
			}
		}else{
			$this->redirectToHome();
		}
    }
}
