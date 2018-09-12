<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Acm\Block\Adminhtml\Edit\Tab;

/**
 * Sitemap edit form
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Meta extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
    }
	
	protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('acm_acm');

        $form = $this->_formFactory->create();

        //$form->setHtmlIdPrefix('acm_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Meta Information')]);
		
		$data = $model->getData();
		
		$fieldset->addField(
            'breadcrumbs',
            'select',
            [
                'label' => __('Show Breadcrumbs'),
                'name' => 'breadcrumbs',
                'options' => ['1' => __('Yes'), '0' => __('No')]
            ]
        );
		
		$fieldset->addField(
            'meta_keyword',
            'textarea',
            [
                'label' => __('Meta Keywords'),
                'name' => 'meta_keyword'
            ]
        );
		
		$fieldset->addField(
            'meta_description',
            'textarea',
            [
                'label' => __('Meta Description'),
                'name' => 'meta_description'
            ]
        );
		
        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }

	/**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Meta Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Meta Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
