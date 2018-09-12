<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Acm\Block\Adminhtml\Edit;

/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
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
        $this->setId('acm_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Advanced Content'));
    }
	
	public function getModel(){
		return $this->_objectManager->create('MGS\Acm\Model\Acm');
	}
	
	protected function _beforeToHtml()
    {
		$this->addTab(
			'main_section',
			[
				'label' => __('General Information'),
				'content' => $this->getLayout()->createBlock('MGS\Acm\Block\Adminhtml\Edit\Tab\Main')->toHtml(),
			]
		);
		
		if($id = $this->getRequest()->getParam('id')){
			
			$this->addTab(
				'page',
				[
					'label' => __('Manage Fields'),
					'url' => $this->getUrl('adminhtml/acm/fields', ['_current' => true, 'id'=>$this->getRequest()->getParam('id')]),
					'class' => 'ajax',
				]
			);
			
			$contentType = $this->getModel()->load($id);
			if($contentType->getContentType() == 2){
				$this->addTab(
					'form_section',
					[
						'label' => __('Form Information'),
						'content' => $this->getLayout()->createBlock('MGS\Acm\Block\Adminhtml\Edit\Tab\Forminfo')->toHtml(),
					]
				);
			}
		}
		
		$this->addTab(
			'layout_section',
			[
				'label' => __('Page Layout'),
				'content' => $this->getLayout()->createBlock('MGS\Acm\Block\Adminhtml\Edit\Tab\Layout')->toHtml(),
			]
		);
		
		$this->addTab(
			'meta_section',
			[
				'label' => __('Meta Information'),
				'content' => $this->getLayout()->createBlock('MGS\Acm\Block\Adminhtml\Edit\Tab\Meta')->toHtml(),
			]
		);
		
		if($id = $this->getRequest()->getParam('id')){
			
			$this->addTab(
				'template_section',
				[
					'label' => __('Template'),
					'content' => $this->getLayout()->createBlock('MGS\Acm\Block\Adminhtml\Edit\Tab\Template')->toHtml(),
				]
			);
		}
		
		
		
        return parent::_beforeToHtml();
    }
}
