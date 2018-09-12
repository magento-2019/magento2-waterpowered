<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Acm\Controller\Adminhtml\Acm;

use Magento\Backend\App\Action;

class Deleteitem extends \MGS\Acm\Controller\Adminhtml\Item
{
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
		$resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');
		if ($id) {
            try {
                $model = $this->_objectManager->create('MGS\Acm\Model\Item');
                $model->setId($id);
                $model->load($id);
				$typeId = $model->getAcmTypeId();
				$model->delete();
				$this->messageManager->addSuccess(__('You deleted an item'));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $resultRedirect->setPath('*/*/item',['type_id'=>$typeId]);
    }
}
