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
use Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder;
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
	/**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;
	
	/**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
	
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
	
	/** @var UrlBuilder */
    protected $actionUrlBuilder;

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
		\Magento\Framework\ObjectManagerInterface $objectManager,
		UrlBuilder $actionUrlBuilder,
        array $data = []
    ) {
		$this->_wysiwygConfig = $wysiwygConfig;
        $this->_systemStore = $systemStore;
		$this->_objectManager = $objectManager;
		$this->actionUrlBuilder = $actionUrlBuilder;
        parent::__construct($context, $registry, $formFactory, $data);
    }
	
	protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('acm_acm');

        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('acm_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General Information')]);
		
		$data = $model->getData();

		$identifier = 'identifier';
		
        if ($model->getId()) {
            $fieldset->addField('acm_id', 'hidden', ['name' => 'acm_id']);
			$identifier = $data['identifier'];
        }
		
        $fieldset->addField(
            'title',
            'text',
            [
                'label' => __('Title'),
                'name' => 'title',
                'required' => true
            ]
        );
		
		
		$baseUrl = $this->actionUrlBuilder->getUrl('',null,null);
		//$url = $this->actionUrlBuilder->getUrl($identifier,null,null);
		$url = explode('?SID=',$baseUrl);
		
		$url = $url[0].'<span id="router-change">'.$identifier.'</span>/';
		
		$note = __('Router of page, full url of page will be:<br/><strong>%1</strong>', $url);
		
		$fieldset->addField(
            'identifier',
            'text',
            [
                'label' => __('Identifier'),
                'name' => 'identifier',
                'required' => true,
                'class' => 'validate-identifier',
				'note' => $note
            ]
        );
		
		if($this->getRequest()->getParam('id')){
			$fieldset->addField(
				'content_type',
				'select',
				[
					'label' => __('Type'),
					'name' => 'content_type',
					'required' => false,
					'options' => ['1' => __('Advanced Content'), '2' => __('Custom Form')],
					'readonly' => 'readonly'
				]
			);
		}else{
			$fieldset->addField(
				'content_type',
				'select',
				[
					'label' => __('Type'),
					'name' => 'content_type',
					'required' => false,
					'options' => ['1' => __('Advanced Content'), '2' => __('Custom Form')]
				]
			);
		}
		
		$fieldset->addField(
            'description',
            'editor',
            [
                'name' => 'description',
                'label' => __('Description'),
                'title' => __('Description'),
                'style' => 'height:15em',
                'required' => false,
                'config' => $this->_wysiwygConfig->getConfig()
            ]
        );
		
		$fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'name' => 'status',
                'required' => false,
                'options' => ['1' => __('Enabled'), '0' => __('Disabled')]
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
