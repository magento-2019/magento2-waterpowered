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
class Template extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
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
        array $data = []
    ) {
		$this->_wysiwygConfig = $wysiwygConfig;
        $this->_systemStore = $systemStore;
		$this->_objectManager = $objectManager;
        parent::__construct($context, $registry, $formFactory, $data);
    }
	
	protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('MGS_Acm::template.phtml');
    }
	
	public function getModel(){
		$model = $this->_coreRegistry->registry('acm_acm');
		return $model;
	}
	
	public function getFields($typeId){
		$fields = $this->_objectManager->create('MGS\Acm\Model\Field')
			->getCollection()
			->addFieldToFilter('acm_type_id', $typeId);
		return $fields;
	}
	
	public function hasProducts($typeId){
		$fields = $this->_objectManager->create('MGS\Acm\Model\Field')
			->getCollection()
			->addFieldToFilter('acm_type_id', $typeId)
			->addFieldToFilter('type', 'products');
		if(count($fields)>0){
			return true;
		}
		return false;
	}
	
	public function getDefaultFormTemplate(){
		return 'forms/form_base.phtml';
	}
	
	public function getDefaultListTemplate(){
		return 'advanced_content/list/list_base.phtml';
	}
	
	public function getDefaultProductsTemplate(){
		return 'advanced_content/product/products_base.phtml';
	}
	
	public function getDefaultDetailTemplate(){
		return 'advanced_content/view/view_base.phtml';
	}
	
	/**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Template');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Template');
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
