<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Acm\Block\Adminhtml\Edit;

/**
 * Sitemap edit form
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
	/**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
		if($this->getRequest()->getParam('type_id')){
			$form = $this->_formFactory->create(
				['data' => ['id' => 'edit_form', 'action' => $this->getUrl('adminhtml/acm/saveitem'), 'method' => 'post', 'enctype' => 'multipart/form-data']]
			);
		}else{
			$form = $this->_formFactory->create(
				['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post', 'enctype' => 'multipart/form-data']]
			);
		}
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
