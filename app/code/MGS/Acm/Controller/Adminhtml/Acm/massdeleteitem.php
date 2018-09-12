<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Acm\Controller\Adminhtml\Acm;

use Magento\Backend\App\Action;

class massdeleteitem extends \MGS\Acm\Controller\Adminhtml\Acm
{
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
		$resultRedirect = $this->resultRedirectFactory->create();
        $ids = $this->getRequest()->getPost('item');
		if(!is_array($ids)) {
            $this->messageManager->addError(__('Please select item(s).'));
        } else {
            try {
                foreach ($ids as $id) {
					$model = $this->_objectManager->create('MGS\Acm\Model\Item');
					$model->setId($id);
					$model->load($id);
					$model->delete();
                }
				$this->messageManager->addSuccess(__('Total of %1 record(s) were successfully deleted.', count($ids)));
                
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $resultRedirect->setPath('*/*/item', ['type_id'=>$this->getRequest()->getParam('type_id')]);
    }
}
