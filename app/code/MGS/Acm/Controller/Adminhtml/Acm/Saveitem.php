<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Acm\Controller\Adminhtml\Acm;

use Magento\Backend\App\Action;
class Saveitem extends \MGS\Acm\Controller\Adminhtml\Acm
{
	/**
     * @var \Magento\Framework\View\Model\Layout\Update\ValidatorFactory
     */
    protected $validatorFactory;
	
	/**
     * @param Action\Context $context
     * @param PostDataProcessor $dataProcessor
     */
    public function __construct(Action\Context $context, \Magento\Framework\View\Model\Layout\Update\ValidatorFactory $validatorFactory)
    {
        $this->validatorFactory = $validatorFactory;
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
        // check if data sent
        $data = $this->getRequest()->getPostValue();
        if ($data) {
			foreach($data as $key=>$value){
				if(isset($data[$key]['delete']) && ($data[$key]['delete'] == 1)){
					unset($data[$key]);
					unset($data['item'][$key]);
				}
			}
            $id = $this->getRequest()->getParam('id');
            $model = $this->_objectManager->create('MGS\Acm\Model\Item')->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addError(__('This item no longer exists.'));
                return $resultRedirect->setPath('*/*/item', ['type_id'=>$data['acm_type_id']]);
            }
			
			if (!empty($data['layout_update_xml'])) {
				/** @var $validatorCustomLayout \Magento\Framework\View\Model\Layout\Update\Validator */
				$validatorCustomLayout = $this->validatorFactory->create();
				if (!empty($data['layout_update_xml']) && !$validatorCustomLayout->isValid($data['layout_update_xml'])) {
					$errorNo = false;
					$data['layout_update_xml'] = '';
				}
				
				foreach ($validatorCustomLayout->getMessages() as $message) {
					$this->messageManager->addError($message);
				}
			}
			
			$identifier = $data['url_key'];
			if($identifier!=''){
				$search = $this->_objectManager->create('MGS\Acm\Model\Item')
					->getCollection()
					->addFieldToFilter('url_key', $identifier);
					
				if(isset($data['item_id']) && $data['item_id']!=''){
					$search->addFieldToFilter('item_id', ['neq'=>$data['item_id']]);
				}
				
				if((count($search)>0)){
					$data['url_key'] = '';
					$this->messageManager->addNotice(__('Url Key "%1" already exists.', $identifier));
				}
			}

            // init model and set data
			
			$jsHelper = $this->_objectManager->create('Magento\Backend\Helper\Js');
			if (isset($data['product_ids'])) {
				$productIds = $jsHelper->decodeGridSerializedInput($data['product_ids']);
				$data['product_ids'] = $productIds;
			}

            $model->setData($data);
			

            // try to save it
            try {
                // save the data
                $model->save();
				
                // display success message
                $this->messageManager->addSuccess(__('You saved the item.'));
                // clear previously saved data from session
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);

                // check if 'Save and Continue'
                if ($back = $this->getRequest()->getParam('back')) {
					if($back=='new'){
						return $resultRedirect->setPath('*/*/newitem', ['type_id'=>$data['acm_type_id']]);
					}
                    return $resultRedirect->setPath('*/*/edititem', ['type_id'=>$data['acm_type_id'], 'id' => $model->getId()]);
                }
                // go to grid
                return $resultRedirect->setPath('*/*/item', ['type_id'=>$data['acm_type_id']]);
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // save data in session
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData($data);
                // redirect to edit form
                return $resultRedirect->setPath('*/*/edititem', ['type_id'=>$data['acm_type_id'], 'id' => $this->getRequest()->getParam('id')]);
            }
        }
        return $resultRedirect->setPath('*/*/item', ['type_id'=>$data['acm_type_id']]);
    }
}
