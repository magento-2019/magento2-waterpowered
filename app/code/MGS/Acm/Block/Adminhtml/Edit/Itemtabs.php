<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Acm\Block\Adminhtml\Edit;

/**
 * Admin page left menu
 */
class Itemtabs extends \Magento\Backend\Block\Widget\Tabs
{
	/**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
	
	/**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $_jsonEncoder;
	
	 /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;
	
	/**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_objectManager = $objectManager;
        parent::__construct($context, $jsonEncoder, $authSession);
    }
	
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
		$model = $this->getModel();
        $this->setId('acm_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($model->getTitle());
    }
	
	public function getModel(){
		return $this->_objectManager->create('MGS\Acm\Model\Acm')->load($this->getRequest()->getParam('type_id'));
	}
	
	protected function _beforeToHtml()
    {
		$model = $this->getModel();
		$this->addTab(
			'main_section',
			[
				'label' => __('%1 Information', $model->getTitle()),
				'content' => $this->getLayout()->createBlock('MGS\Acm\Block\Adminhtml\Edit\Tab\Item')->toHtml(),
			]
		);
		
		if($model->getContentType()==1){
			$this->addTab(
				'layout_section',
				[
					'label' => __('Page Layout'),
					'content' => $this->getLayout()->createBlock('MGS\Acm\Block\Adminhtml\Edit\Tab\Itemlayout')->toHtml(),
				]
			);
			
			$this->addTab(
				'meta_section',
				[
					'label' => __('Meta Information'),
					'content' => $this->getLayout()->createBlock('MGS\Acm\Block\Adminhtml\Edit\Tab\Itemmeta')->toHtml(),
				]
			);
		}
		
		$field = $this->_objectManager->create('MGS\Acm\Model\Field')
			->getCollection()
			->addFieldToFilter('acm_type_id', $model->getId())
			->addFieldToFilter('type', 'products')
			->getFirstItem();
		if($field->getId()){
			$this->addTab(
				'product_section',
				[
					'label' => $field->getTitle(),
					'url' => $this->getUrl('adminhtml/acm/product', ['_current' => true]),
					'class' => 'ajax'
				]
			);
		}
		
        return parent::_beforeToHtml();
    }
}
