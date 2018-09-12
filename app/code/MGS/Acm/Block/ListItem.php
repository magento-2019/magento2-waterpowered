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
class ListItem extends Template
{	
	/**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;
	
	/**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $_acmDataHelper;
	
	/**
     * @var \MGS\Acm\Model\ResourceModel\Item\CollectionFactory
     */
	protected $_itemFactory;
	
	/**
     * @var \MGS\Acm\Model\ResourceModel\Item\CollectionFactory
     */
	protected $_fieldFactory;
	
	protected $_acmModel;
	protected $_typeId;
	
	/**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
	
    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
		\MGS\Acm\Helper\Data $acmDataHelper,
		\MGS\Acm\Model\ResourceModel\Item\CollectionFactory $itemFactory,
		\MGS\Acm\Model\ResourceModel\Field\CollectionFactory $fieldFactory,
		\Magento\Cms\Model\Template\FilterProvider $filterProvider,
        array $data = []
    ) {
		$this->_acmDataHelper = $acmDataHelper;
		$this->_itemFactory = $itemFactory;
		$this->_coreRegistry = $context->getRegistry();
		$this->_fieldFactory = $fieldFactory;
		$this->_filterProvider = $filterProvider;
        parent::__construct($context, $data);
    }
	
	public function _construct()
    {
		if($this->_coreRegistry->registry('acm_acm')){
			$this->_acmModel = $this->_coreRegistry->registry('acm_acm');
			$this->_typeId = $this->_acmModel->getId();
		}else{
			if($this->hasData('type_id')){
				$this->_typeId = $this->getData('type_id');
				$this->_acmModel = $this->_acmDataHelper->getModel('MGS\Acm\Model\Acm')->load($this->_typeId);
			}else{
				return;
			}
		}
		
        parent::_construct();
        $itemCollection = $this->_itemFactory->create();
		
		$filter = '';
		if($this->hasData('filter')){
			$filter = $this->_acmDataHelper->convertJson($this->getData('filter'));
		}
		
		$fields = $this->_fieldFactory->create()
			->addFieldToFilter('acm_type_id', $this->_typeId)
			->addFieldToFilter('type', 'store')
			->addFieldToFilter('is_required', 1);
		
		if(count($fields)>0){
			$itemCollection->addFrontendFilter($this->_typeId, $filter, $this->_acmDataHelper->getStore()->getId());
		}else{
			$itemCollection->addFrontendFilter($this->_typeId, $filter);
		}
		
        $this->setCollection($itemCollection);
    }
	
	protected function _prepareLayout()
    {
        if ($this->getCollection()) {
			if($this->hasData('name')){
				$pager = $this->getLayout()->createBlock(
					'Magento\Theme\Block\Html\Pager',
					'acm.list.pager.'.$this->getData('name')
				);
			}else{
				$pager = $this->getLayout()->createBlock(
					'Magento\Theme\Block\Html\Pager',
					'acm.list.pager.'.$this->_typeId
				);
			}
            
			$pageSize = 10;
			if($this->_acmModel->getPageSize()!=''){
				$pageSize = $this->_acmModel->getPageSize();
			}
			if($this->hasData('limit')){
				$pageSize = $this->getData('limit');
			}
            $pager->setLimit($pageSize)->setCollection(
                $this->getCollection()
            );
            $this->setChild('pager', $pager);
        }
        return parent::_prepareLayout();
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
	
	public function setCollection($collection)
    {
        $this->_collection = $collection;
        return $this->_collection;
    }

    public function getCollection()
    {
        return $this->_collection;
    }
	
	/**
     * Get Content data
     * return an array $data
     */
	public function getContentData(){
		$data = $this->_acmModel->getData();
		unset($data['form_captcha'], $data['form_legend'], $data['form_note'], $data['success_message'], $data['template_id'], $data['reply_email'], $data['form_action'], $data['email'], $data['template'], $data['template_detail'], $data['creation_time'], $data['update_time'], $data['template_products'], $data['template_products_detail'], $data['page_size'], $data['breadcrumbs']);
		return $data;
	}
	
	/**
     * Get item collection
     */
	public function getItemCollection(){
		$collection = $this->getCollection();
		$this->_processCollection($collection);
		return $collection;
	}
	
	/**
     * Change data for items
     */
	protected function _processCollection($collection)
    {
		$fields = $this->_fieldFactory->create()
			->getShortInfo($this->_typeId);
		
		$fieldData = [];
		foreach($fields as $field){
			$fieldData[$field['identifier']] = $field['type'];
		}
		
        foreach ($collection as $item) {
			$itemData = $item->getData();
			$data = ['item_id'=>$item->getId(), 'url_key'=>$item->getUrlKey()];
			foreach($fieldData as $identifier => $type){
				if($type!='store'){
					$data[$identifier] = $this->changeItemData($item, $type, $item->getData($identifier));
				}
			}
			$item->setData($data);
        }

        return $collection;
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
				return $this->getLayout()->createBlock('MGS\Acm\Block\Product')->setItem($item)->setTemplate($this->_acmModel->getTemplateProducts())->toHtml();
			case 'textarea':
				return $this->filterContent($value);
			default:
				return $value;
		}
	}
	
	/**
     * Get details url
     * @param $item
     */
	public function getDetailUrl($item){
		$model = $this->_acmModel;
		$path = '';
		if($item->getUrlKey()!=''){
			$path = $model->getIdentifier().'/'.$item->getUrlKey();
			return $this->getUrl($path);
		}else{
			return $this->getUrl('acm/index/view', ['type_id'=>$model->getId(), 'item_id'=>$item->getItemId()]);
		}
	}
	
	public function filterContent($content){
		return $this->_filterProvider->getBlockFilter()->filter($content);
	}
}

