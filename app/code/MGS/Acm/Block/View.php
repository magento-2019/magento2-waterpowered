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
class View extends Template
{
	/**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;
	
	/**
     * @var \MGS\Acm\Model\ResourceModel\Item\CollectionFactory
     */
	protected $_fieldFactory;
	
	protected $_coreRegistry;
	
	protected $_item;
	protected $_type;
	protected $_title;
	
    /**
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
		Template\Context $context, array $data = [],
		\Magento\Framework\Registry $coreRegistry,
		\Magento\Cms\Model\Template\FilterProvider $filterProvider,
		\MGS\Acm\Model\ResourceModel\Field\CollectionFactory $fieldFactory
	){
		$this->_coreRegistry = $coreRegistry;
		$this->_fieldFactory = $fieldFactory;
		$this->_filterProvider = $filterProvider;
		parent::__construct($context, $data);
    }
	
	/**
     * @return void
     */
    protected function _construct()
    {
		if($this->_coreRegistry->registry('current_item')){
			$this->_item = $this->_coreRegistry->registry('current_item');
			$this->_type = $this->_coreRegistry->registry('current_content');
			
			$fields = $this->_fieldFactory->create()
				->getShortInfo($this->_item->getAcmTypeId());
			$fieldData = [];
			foreach($fields as $field){
				$fieldData[$field['identifier']] = $field['type'];
			}
			
			$itemData = $this->_item->getData();
			
			foreach($itemData as $identifier=>$value){
				if(isset($fieldData[$identifier])){
					$itemData[$identifier] = $this->changeItemData($this->_item, $fieldData[$identifier], $value);
				}
			}
			
			$this->_item->setData($itemData);
		}else{
			return;
		}
        parent::_construct();
        $this->setTemplate('MGS_Acm::'.$this->_type->getTemplateDetail());
		
    }
	
	protected function _prepareLayout()
    {
		$this->_title = $this->_item->getPageTitle();
		if($this->_title==''){
			$this->_title = $this->_type->getTitle() . ' ' . $this->_item->getId();
		}
        $this->pageConfig->getTitle()->set($this->_title);
		
		$keywords = $this->_item->getMetaKeyword();
		if($keywords==''){
			$keywords = $this->_type->getMetaKeyword();
		}
		if($keywords!=''){
			$this->pageConfig->setKeywords($keywords);
		}
		
		$description = $this->_item->getMetaDescription();
		if($description==''){
			$description = $this->_type->getMetaDescription();
		}
		
		if($description!=''){
			$this->pageConfig->setDescription($description);
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
		$model = $this->_type;
        if ($model->getBreadcrumbs() && ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs'))) {
            $breadcrumbsBlock->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            );
			
			$breadcrumbsBlock->addCrumb(
                $model->getIdentifier(),
                [
                    'label' => __($model->getTitle()),
                    'title' => __($model->getTitle()),
                    'link' => $this->getUrl($model->getIdentifier())
                ]
            );
			
            $breadcrumbsBlock->addCrumb('acm_list_page', ['label' => __($this->_title), 'title' => __($this->_title)]);
        }
    }
	
	public function changeItemData($item, $type, $value){
		switch($type){
			case 'date':
				if($value!='1970-01-01'){
					return $this->formatDate($value, \IntlDateFormatter::LONG);
				}
				return '';
			case 'file':
			case 'image':
				if($value!=''){
					return $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $value;
				}
				return '';
			case 'radios':
			case 'checkboxes':
			case 'multiselect':
				return str_replace(',',', ',$value);
			case 'products':
				return $this->getLayout()->createBlock('MGS\Acm\Block\Product')->setItem($item)->setTemplate($this->_type->getTemplateProductsDetail())->toHtml();
			case 'textarea':
				return $this->_filterProvider->getBlockFilter()->filter($value);
			default:
				return $value;
		}
	}
	
	public function getDataInfo($identifier){
		return $this->_item->getData($identifier);
	}
	
	/**
     * Get details url
     * @param $item
     */
	public function getDetailUrl(){
		$model = $this->_type;
		$path = '';
		if($this->_item->getUrlKey()!=''){
			$path = $model->getIdentifier().'/'.$this->_item->getUrlKey();
			return $this->getUrl($path);
		}else{
			return $this->getUrl('acm/index/view', ['type_id'=>$model->getId(), 'item_id'=>$this->_item->getItemId()]);
		}
	}
}

