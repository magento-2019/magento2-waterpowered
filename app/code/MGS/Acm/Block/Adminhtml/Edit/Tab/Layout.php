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
class Layout extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
	/**
     * @var \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface
     */
    protected $pageLayoutBuilder;
	
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
		\Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
		$this->pageLayoutBuilder = $pageLayoutBuilder;
    }
	
	protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('acm_acm');

        $form = $this->_formFactory->create();

        //$form->setHtmlIdPrefix('acm_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Page Layout')]);
		
		$data = $model->getData();
		
		$fieldset->addField(
            'layout',
            'select',
            [
                'label' => __('Layout'),
                'name' => 'layout',
                'required' => false,
                'values' => $this->pageLayoutBuilder->getPageLayoutsConfig()->toOptionArray()
            ]
        );
		
		$fieldset->addField(
            'layout_update_xml',
            'textarea',
            [
                'name' => 'layout_update_xml',
                'label' => __('Layout Update XML'),
                'title' => __('Layout Update XML'),
                'style' => 'height:24em'
            ]
        );
		
		if(!$this->getRequest()->getParam('id')){
			$data['layout'] = '1column';
		}
		
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
