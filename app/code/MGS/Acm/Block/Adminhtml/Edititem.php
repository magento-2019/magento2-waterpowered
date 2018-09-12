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
class Edititem extends \Magento\Backend\Block\Widget\Form\Container
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

		$this->buttonList->remove('save');
		
		$check = true;
		if($model = $this->_coreRegistry->registry('acm_type')){
			if($model->getContentType()==2){
				$check = false;
			}
		}
		if($check){
			$this->getToolbar()->addChild(
				'save-split-button',
				'Magento\Backend\Block\Widget\Button\SplitButton',
				[
					'id' => 'save-split-button',
					'label' => __('Save & Edit'),
					'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
					'button_class' => 'widget-button-save',
					'options' => $this->_getSaveSplitButtonOptions()
				]
			);
		}

		$this->buttonList->update('delete', 'label', __('Delete'));
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
		
		$options[] = [
			'label' => __('Save & New'),
			'class' => 'save',
			'data_attribute' => [
				'mage-init' => ['button' => ['event' => 'saveAndNew', 'target' => '#edit_form']],
			]
		];
		
        
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
     * Get edit form container header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {

		return __('New Item');

    }
	
	public function getBackUrl()
    {
        return $this->getUrl('*/*/item', ['type_id' => $this->getRequest()->getParam('type_id')]);
    }
	
	public function getDeleteUrl()
    {
        return $this->getUrl('*/*/deleteitem', [$this->_objectId => $this->getRequest()->getParam($this->_objectId)]);
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
