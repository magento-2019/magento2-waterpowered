<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Acm\Block;

use Magento\Framework\View\Element\Template;

/**
 * Main contact form block
 */
class Acm extends Template
{
	/**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
	
	protected $_type;
	
	protected $_coreRegistry;
	
    /**
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
		Template\Context $context, array $data = [],
		\Magento\Framework\Registry $coreRegistry,
		\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        parent::__construct($context, $data);
		$this->_objectManager = $objectManager;
		$this->_coreRegistry = $coreRegistry;
    }
	
	/**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('MGS_Acm::advanced_content.phtml');
		
    }
	
	protected function _prepareLayout()
    {
		$this->_contentType = $this->_coreRegistry->registry('acm_acm');
        $this->pageConfig->getTitle()->set($this->_contentType->getTitle());
		if($this->_contentType->getMetaKeyword()!=''){
			$this->pageConfig->setKeywords($this->_contentType->getMetaKeyword());
		}
		
		if($this->_contentType->getMetaDescription()!=''){
			$this->pageConfig->setDescription($this->_contentType->getMetaDescription());
		}
		
		$this->_addBreadcrumbs();

        return parent::_prepareLayout();
    }
	
	/**
     * Prepare breadcrumbs
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function _addBreadcrumbs()
    {
		$model = $this->_contentType;
        if ($model->getBreadcrumbs() && ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs'))) {
            $breadcrumbsBlock->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            );
            $breadcrumbsBlock->addCrumb('acm_list_page', ['label' => __($model->getTitle()), 'title' => __($model->getTitle())]);
        }
    }
	
	public function getType(){
		return $this->_contentType;
	}
	
	public function getModel(){
		return $this->_objectManager->create('MGS\Acm\Model\Acm');
	}
	
	public function getCustomTemplate(){
		
	}
}

