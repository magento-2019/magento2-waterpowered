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
class Forminfo extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
	/**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;
	
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
	
	/**
     * @var \Magento\Email\Model\ResourceModel\Template\CollectionFactory
     */
    protected $_templatesFactory;
	
	/**
     * @var \MGS\Acm\Model\ResourceModel\Item\CollectionFactory
     */
	protected $_fieldFactory;

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
        \Magento\Store\Model\System\Store $systemStore,
		\Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
		\Magento\Email\Model\ResourceModel\Template\CollectionFactory $templatesFactory,
		\MGS\Acm\Model\ResourceModel\Field\CollectionFactory $fieldFactory,
        array $data = []
    ) {
		$this->_wysiwygConfig = $wysiwygConfig;
        $this->_systemStore = $systemStore;
		$this->_templatesFactory = $templatesFactory;
		$this->_fieldFactory = $fieldFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }
	
	protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('acm_acm');

        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('acm_forminfo_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Form Information')]);
		
		$data = $model->getData();
		
        $actionField = $fieldset->addField(
			'form_action',
			'select',
			[
				'label' => __('Form Action'),
				'name' => 'form_action',
				'required' => false,
				'options' => [1 => __('Send Email & Save Info'), 2 => __('Send Email'), 3 => __('Save Info')]
			]
		);
		
		$emailField = $fieldset->addField(
            'email',
            'text',
            [
                'label' => __('Send Email to'),
                'name' => 'email',
                'required' => true,
				'note' => __('Comma-separated')
            ]
        );
		
		$fields = $this->_fieldFactory->create()
			->addFieldToFilter('acm_type_id', $model->getId())
			->addFieldToFilter('type', 'text');
		
		$fieldOption = [''=>__('Choose a field')];
		if(count($fields)>0){
			foreach($fields as $field){
				$fieldOption[$field->getIdentifier()] = $field->getTitle();
			}
		}
		
		$replyField = $fieldset->addField(
            'reply_email',
            'select',
            [
                'label' => __('Set field to reply'),
                'name' => 'reply_email',
				'options' => $fieldOption
            ]
        );
		
		$collection = $this->_templatesFactory->create();
		$collection->load();
		$templateOption = [];
		if(count($collection)>0){
			foreach($collection as $template){
				$templateOption[$template->getId()] = $template->getTemplateCode();
			}
		}
		
		$templatEmail = $fieldset->addField(
			'template_id',
			'select',
			[
				'label' => __('Template Email'),
				'name' => 'template_id',
				'options' => $templateOption
			]
		);
		
		$fieldset->addField(
			'form_captcha',
			'select',
			[
				'label' => __('Enable CAPTCHA'),
				'name' => 'form_captcha',
				'options' => [1 => __('Yes'), 0 => __('No')]
			]
		);
		
		$fieldset->addField(
            'form_legend',
            'text',
            [
                'label' => __('Form Legend'),
                'name' => 'form_legend'
            ]
        );
		
		$fieldset->addField(
            'form_note',
            'textarea',
            [
                'label' => __('Note'),
                'name' => 'form_note'
            ]
        );
		
		$fieldset->addField(
            'success_message',
            'textarea',
            [
                'label' => __('Success Message'),
                'name' => 'success_message'
            ]
        );

        $form->setValues($data);
        $this->setForm($form);
		
		$this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                'MGS\Acm\Block\Widget\Form\Element\Dependence'
            )->addFieldMap(
                $actionField->getHtmlId(),
                $actionField->getName()
            )->addFieldMap(
                $emailField->getHtmlId(),
                $emailField->getName()
            )->addFieldDependence(
				$emailField->getName(),
                $actionField->getName(),
				"1,2"
            )->addFieldMap(
                $actionField->getHtmlId(),
                $actionField->getName()
            )->addFieldMap(
                $templatEmail->getHtmlId(),
                $templatEmail->getName()
            )->addFieldDependence(
				$templatEmail->getName(),
                $actionField->getName(),
				"1,2"
            )->addFieldMap(
                $actionField->getHtmlId(),
                $actionField->getName()
            )->addFieldMap(
                $replyField->getHtmlId(),
                $replyField->getName()
            )->addFieldDependence(
				$replyField->getName(),
                $actionField->getName(),
				"1,2"
            )
        );

        return parent::_prepareForm();
    }

	/**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('General Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('General Information');
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
