<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Acm\Controller\Adminhtml\Acm;

use Magento\Backend\App\Action;
class Generate extends \MGS\Acm\Controller\Adminhtml\Acm
{
	/**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $_acmDataHelper;
	
	/**
     * @param Action\Context $context
     * @param PostDataProcessor $dataProcessor
     */
    public function __construct(
		Action\Context $context,
		\MGS\Acm\Helper\Data $acmDataHelper
	){
		$this->_acmDataHelper = $acmDataHelper;
        parent::__construct($context);
    }
	
    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id = $this->getRequest()->getParam('id')) {
            $model = $this->_objectManager->create('MGS\Acm\Model\Acm')->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addError(__('This item no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
            try {
				$this->_acmDataHelper->gerateTemplateFiles($model);
				
                // display success message
                $this->messageManager->addSuccess(__('you have successfully generated template files'));
                
				return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
