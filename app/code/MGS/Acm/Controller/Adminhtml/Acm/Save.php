<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Acm\Controller\Adminhtml\Acm;

use Magento\Backend\App\Action;
class Save extends \MGS\Acm\Controller\Adminhtml\Acm
{
	/**
     * @var \Magento\Framework\View\Model\Layout\Update\ValidatorFactory
     */
    protected $validatorFactory;
	
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
		\Magento\Framework\View\Model\Layout\Update\ValidatorFactory $validatorFactory,
		\MGS\Acm\Helper\Data $acmDataHelper
	){
        $this->validatorFactory = $validatorFactory;
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
        // check if data sent
        $data = $this->getRequest()->getPostValue();
		
		
		
		if(isset($data['field']['{{number}}'])){
			unset($data['field']['{{number}}']);
		}
        if ($data) {
            $id = $this->getRequest()->getParam('id');
			
			$isNew = true;
			if(isset($data['acm_id']) && ($data['acm_id']!='')){
				$isNew = false;
			}
			
			$identifier = $data['identifier'];
			if($identifier!=''){
				$search = $this->_objectManager->create('MGS\Acm\Model\Acm')
					->getCollection()
					->addFieldToFilter('identifier', $identifier);
					
				if(!$isNew){
					$search->addFieldToFilter('acm_id', ['neq'=>$data['acm_id']]);
				}
				
				if((count($search)>0)){
					$this->messageManager->addError(__('Identifier name "%1" already exists.', $identifier));
					return $resultRedirect->setPath('*/*/');
				}
			}
			
            $model = $this->_objectManager->create('MGS\Acm\Model\Acm')->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addError(__('This item no longer exists.'));
                return $resultRedirect->setPath('*/*/');
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
			if($isNew && ($data['content_type']==1)){
				$data['template'] = 'advanced_content/'.$data['identifier'].'/list_base.phtml';
				$data['template_detail'] = 'advanced_content/'.$data['identifier'].'/view_base.phtml';
			}

            // init model and set data
            $model->setData($data);

            // try to save it
            try {
                // save the data
                $model->save();
				
				if(isset($data['field']) && (count($data['field'])>0)){
					foreach($data['field'] as $field){
						if(isset($field['title']) && isset($field['identifier']) && ($field['title'] != '') && ($field['identifier'] != '')){
							if($field['type']=='store'){
								$field['identifier'] = 'store_id';
							}
							if(isset($field['additional_content']) && ($field['additional_content']!='')){
								$additionalContent = json_encode($field['additional_content']);
								$field['additional_content'] = $additionalContent;
							}else{
								$field['additional_content'] = '';
							}
							$fieldId = '';
							if(isset($field['field_id']) && ($field['field_id']!='')){
								$fieldId = $field['field_id'];
							}
							unset($field['field_id']);
							$field['acm_type_id'] = $model->getId();
							$fieldModel = $this->_objectManager->create('MGS\Acm\Model\Field');
							$fieldModel->setData($field);
							if($fieldId!=''){
								$fieldModel->setId($fieldId);
							}
							$fieldModel->save();
						}
					}
				}
				
				if(isset($data['field']['remove'])){
					$remove = array_filter($data['field']['remove']);
					if(count($remove)>0){
						foreach($remove as $removeItemId){
							$this->_objectManager->create('MGS\Acm\Model\Field')
								->load($removeItemId)
								->delete();
						}
					}
				}
					
				if($isNew){
					if($model->getContentType()==1){
						$this->_acmDataHelper->gerateTemplateFiles($model);
					}else{
						if($model->getFormAction()!=3){
							$templateId = $this->_acmDataHelper->gerateTemplateEmail($model);
							$model->setTemplateId($templateId)->save();
						}
					}
				}
				
                // display success message
                $this->messageManager->addSuccess(__('You saved the item.'));
                // clear previously saved data from session
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);

                // check if 'Save and Continue'
                if ($back = $this->getRequest()->getParam('back')) {
					if($back == 'new'){
						if($model->getContentType()==1){
							$this->_acmDataHelper->gerateTemplateFiles($model);
						}else{
							if($model->getFormAction()!=3){
								$templateId = $this->_acmDataHelper->gerateTemplateEmail($model);
								$model->setTemplateId($templateId)->save();
								$this->messageManager->addSuccess(__('Email template was successfully generated. Click <a href="%1">here</a> to edit.', $this->getUrl('adminhtml/email_template/edit', ['id'=>$templateId])));
							}
						}
					}
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
                }
                // go to grid
                return $resultRedirect->setPath('*/*/index');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // save data in session
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData($data);
                // redirect to edit form
                return $resultRedirect->setPath('*/*/index');
            }
        }
        return $resultRedirect->setPath('*/*/index');
    }
}
