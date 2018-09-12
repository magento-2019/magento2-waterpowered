<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Acm\Block\Adminhtml;

/**
 * Sitemap edit form container
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Init container
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml';
        $this->_blockGroup = 'MGS_Acm';

        parent::_construct();


		$this->buttonList->add(
			'saveandcontinue',
			[
				'label' => __('Save and Continue Edit'),
				'class' => 'save primary',
				'data_attribute' => [
					'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
				]
			],
			-100
		);

		$this->buttonList->remove('save');
		
		if($id = $this->getRequest()->getParam('id')){
			$model = $this->_coreRegistry->registry('acm_acm');
			
			$this->buttonList->remove('saveandcontinue');
			$this->getToolbar()->addChild(
				'save-split-button',
				'Magento\Backend\Block\Widget\Button\SplitButton',
				[
					'id' => 'save-split-button',
					'label' => __('Save & Edit'),
					'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
					'button_class' => 'widget-button-save',
					'options' => $this->_getSaveSplitButtonOptions()
				],
				-100
			);
			
			
		}

		$this->buttonList->update('delete', 'label', __('Delete'));
    }

    /**
     * Get edit form container header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('acm_acm')->getId()) {
            return __('Edit %1', $this->_coreRegistry->registry('acm_acm')->getTitle());
        } else {
            return __('New Item');
        }
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
	
	/**
     * @return string
     */
    public function getSaveAndNewUrl()
    {
        return $this->getUrl(
            'catalog/*/save',
            ['new' => true]
        );
    }
	
	/**
     * Get dropdown options for save split button
     *
     * @return array
     */
    protected function _getSaveSplitButtonOptions()
    {
        $options = [];
		
		$options[] = [
			'label' => __('Save & Edit'),
			'class' => 'save',
			'data_attribute' => [
				'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
			],
			'default' => true,
		];
		
		$model = $this->_coreRegistry->registry('acm_acm');
		
		if($model->getContentType()==1){
			$options[] = [
				'label' => __('Save & Generate Template Files'),
				'class' => 'save',
				'data_attribute' => [
					'mage-init' => ['button' => ['event' => 'saveAndNew', 'target' => '#edit_form']],
				]
			];
		}else{
			if($model->getFormAction()!=3){
				$options[] = [
					'label' => __('Save & Generate Template Email'),
					'class' => 'save',
					'data_attribute' => [
						'mage-init' => ['button' => ['event' => 'saveAndNew', 'target' => '#edit_form']],
					]
				];
			}
		}

        $options[] = [
            'label' => __('Save & Close'),
			'class' => 'save',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save', 'target' => '#edit_form']]
			]
        ];
        return $options;
    }
	
	/**
     * Get Save Split Button html
     *
     * @return string
     */
    public function getSaveSplitButtonHtml()
    {
        return $this->getChildHtml('save-split-button');
    }
}
