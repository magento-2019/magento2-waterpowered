<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Acm\Block\Adminhtml\Edit\Tab;

use Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder;

/**
 * Sitemap edit form
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Itemmeta extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
	/** @var UrlBuilder */
    protected $actionUrlBuilder;
	
	/**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
	
	/**
     * @var \MGS\Acm\Model\ResourceModel\Item\CollectionFactory
     */
	protected $_itemFactory;
	
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
		\MGS\Acm\Model\ResourceModel\Item\CollectionFactory $itemFactory,
		UrlBuilder $actionUrlBuilder,
		\Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
		$this->actionUrlBuilder = $actionUrlBuilder;
		$this->_itemFactory = $itemFactory;
		$this->_objectManager = $objectManager;
        parent::__construct($context, $registry, $formFactory, $data);
    }
	
	protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('acm_item');

        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Meta Information')]);
		
		$data = $model->getData();
		
		$fieldset->addField(
            'url_key_temp',
            'hidden',
            [
                'name' => 'url_key_temp'
            ]
        );
		
		$fieldset->addField(
            'url_key_base',
            'hidden',
            [
                'name' => 'url_key_base'
            ]
        );
		
		$fieldset->addField(
            'url_parent',
            'hidden',
            [
                'name' => 'url_parent'
            ]
        );
		
		$tempUrl = $this->actionUrlBuilder->getUrl('acm/index/view',null,null);
		$temUrl = explode('?SID=',$tempUrl);
		$temUrl = $temUrl[0] . 'type_id/'.$this->getRequest()->getParam('type_id').'/id/';
		$id = $this->getNewItemId();
		if($this->getRequest()->getParam('id')){
			$id = $this->getRequest()->getParam('id');
		}
		$data['url_key_temp'] = $temUrl.$id;
		
		
		$baseUrl = $this->actionUrlBuilder->getUrl('',null,null);
		$baseUrl = explode('?SID=',$baseUrl);
		
		$data['url_key_base'] = $baseUrl[0];
		
		$contentType = $this->_objectManager->create('MGS\Acm\Model\Acm')->load($this->getRequest()->getParam('type_id'));
		
		$data['url_parent'] = $contentType->getIdentifier();
		
		if($this->getRequest()->getParam('id') && $data['url_key']!=''){
			$url = '<span id="router-change">'.$baseUrl[0].$contentType->getIdentifier().'/'.$data['url_key'].'</span>/';
		}else{
			$url = '<span id="router-change">'.$baseUrl[0].'acm/index/view/type_id/'.$this->getRequest()->getParam('type_id').'/id/'.$id.'</span>/';
		}
		
		$note = __('Router of page, full url of page will be:<br/><strong>%1</strong>', $url);

        $fieldset->addField(
            'url_key',
            'text',
            [
                'label' => __('Url Key'),
                'name' => 'url_key',
				'class' => 'validate-identifier',
				'note' => $note
            ]
        );
		
		$fieldset->addField(
            'page_title',
            'text',
            [
                'label' => __('Page Title'),
                'name' => 'page_title'
            ]
        );
		
		$fieldset->addField(
            'meta_keyword',
            'textarea',
            [
                'label' => __('Meta Keywords'),
                'name' => 'meta_keyword'
            ]
        );
		
		$fieldset->addField(
            'meta_description',
            'textarea',
            [
                'label' => __('Meta Description'),
                'name' => 'meta_description'
            ]
        );
		
		
		
        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }
	
	public function getNewItemId(){
		$lastItem = $this->_itemFactory->create()->setOrder('item_id', 'DESC')->getFirstItem();
		$newId = (int)$lastItem->getId() + 1;
		return $newId;
	}

	/**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Meta Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Meta Information');
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
