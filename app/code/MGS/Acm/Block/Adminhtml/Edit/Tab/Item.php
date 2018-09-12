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
class Item extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
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
	
	public function getModel($type){
		return $this->_objectManager->create($type);
	}
	
	protected function _prepareForm()
    {
		$typeId = $this->getRequest()->getParam('type_id');
        $model = $this->_coreRegistry->registry('acm_item');
        $fields = $this->getModel('MGS\Acm\Model\Field')
			->getCollection()
			->addFieldToFilter('acm_type_id', $typeId)
			->setOrder('position', 'ASC');

        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('acm_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('%1 Information', $this->getModel('MGS\Acm\Model\Acm')->load($typeId)->getTitle())]);
		
		$data = $model->getData();

        if ($model->getId()) {
            $fieldset->addField('item_id', 'hidden', ['name' => 'item_id']);
        }
		
		$fieldset->addField('type_id', 'hidden', ['name' => 'acm_type_id']);
		
		if(count($fields)>0){
			foreach($fields as $_field){
				if($_field->getType() == 'image' || $_field->getType() == 'file'){
					$this->addField($fieldset, $_field, $data);
					if(isset($data[$_field->getIdentifier()]) && $data[$_field->getIdentifier()]!=''){
						$fieldset->addField(
							'mgs_acm_custom_'.$_field->getIdentifier(),
							'hidden',
							['name' => 'item['.$_field->getIdentifier().']']
						);
						$data['mgs_acm_custom_'.$_field->getIdentifier()] = $data[$_field->getIdentifier()];
					}
				}else{
					$this->addField($fieldset, $_field, $data);
				}
			}
		}
		
		$data['type_id'] = $this->getRequest()->getParam('type_id');
		
        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }
	
	public function addField($fieldset, $_field, $data){
		$type = $_field->getType();
		$index = $_field->getIdentifier();
		switch($type){
			case 'text':
				$arrInfo = $this->addTextField($fieldset, $_field);
				break;
			case 'select':
				$arrInfo = $this->addSelectField($fieldset, $_field);
				break;
			case 'textarea':
				$arrInfo = $this->addTextareaField($fieldset, $_field);
				break;
			case 'image':
				$arrInfo = $this->addImageField($fieldset, $_field);
				break;
			case 'file':
				$arrInfo = $this->addFileField($fieldset, $_field, $data);
				break;
			case 'store':
				$arrInfo = $this->addStoreField($fieldset, $_field);
				break;
			case 'date':
				$arrInfo = $this->addDateField($fieldset, $_field);
				break;
			case 'radios':
				if($_field->getAdditionalContent()!=''){
					$arrInfo = $this->addCheckField($fieldset, $_field);
					break;
				}else{
					return;
				}
			case 'checkboxes':
				if($_field->getAdditionalContent()!=''){
					$arrInfo = $this->addCheckField($fieldset, $_field);
					break;
				}else{
					return;
				}
			case 'multiselect':
				if($_field->getAdditionalContent()!=''){
					$arrInfo = $this->addCheckField($fieldset, $_field);
					break;
				}else{
					return;
				}
			default:
				return;
		}
		if($_field->getAdditionalContent()=='"1"'){
			$type = 'editor';
		}
		
		if($type=='store'){
			if (!$this->_storeManager->isSingleStoreMode()) {
				$type = 'multiselect';
			}else{
				$type = 'hidden';
			}
			$field = $fieldset->addField(
				$index,
				$type,
				$arrInfo
			);
			$renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);
		}else{
			$fieldset->addField(
				$index,
				$type,
				$arrInfo
			);
		}
		
		
		
		return;
	}
	
	public function setDefaultFieldInfo($_field){
		$arrInfo = ['label' => $_field->getTitle(), 'title'=>$_field->getTitle(), 'name' => 'item['.$_field->getIdentifier().']'];
		if($_field->getIsRequired()){
			$arrInfo['required'] = true;
		}
		if($_field->getNote() !=''){
			$arrInfo['note'] = $_field->getNote();
		}
		return $arrInfo;
	}
	
	/* Text Field */
	public function addTextField($fieldset, $_field){
		$arrInfo = $this->setDefaultFieldInfo($_field);
		if($_field->getAdditionalContent()!=''){
			$additionalInfo = json_decode($_field->getAdditionalContent(),true);
			$additionalInfo = implode(' ', $additionalInfo);
			$arrInfo['class'] = $additionalInfo;
		}
		return $arrInfo;
	}
	
	/* Dropdown Field */
	public function addSelectField($fieldset, $_field){
		$arrInfo = $this->setDefaultFieldInfo($_field);
		if($_field->getAdditionalContent()!=''){
			$arrOption = [];
			$additionalInfo = json_decode($_field->getAdditionalContent(),true);
			usort($additionalInfo['option'], function($a, $b) {
				return $a['position'] - $b['position'];
			});

			foreach($additionalInfo['option'] as $key => $_option){
				$value = $_option['value'];
				if($value==''){
					$value = $_option['title'];
				}
				$arrOption[$value] = $_option['title'];
			}
			$arrInfo['options'] = $arrOption;
		}
		return $arrInfo;
	}
	
	/* Textarea Field */
	public function addTextareaField($fieldset, $_field){
		$arrInfo = $this->setDefaultFieldInfo($_field);
		if($_field->getAdditionalContent()!=''){
			$additionalInfo = json_decode($_field->getAdditionalContent(),true);
			if($additionalInfo==1){
				$arrInfo['config'] = $this->_wysiwygConfig->getConfig();
				$arrInfo['style'] = 'height:17em';
			}
		}
		return $arrInfo;
	}
	
	/* Image Upload */
	public function addImageField($fieldset, $_field){
		$arrInfo = $this->setDefaultFieldInfo($_field);
		$arrInfo['name'] = $_field->getIdentifier();
		return $arrInfo;
	}
	
	/* Image Upload */
	public function addFileField($fieldset, $_field, $data){
		$arrInfo = $this->setDefaultFieldInfo($_field);
		$arrInfo['name'] = $_field->getIdentifier();
		if(isset($data[$_field->getIdentifier()]) && ($data[$_field->getIdentifier()] != '')){
			$fileName = explode('/', $data[$_field->getIdentifier()]);
			$exName = explode('.', end($fileName));
			$arrInfo['after_element_html'] = '<span class="delete-file"><input type="checkbox" id="acm_'.$_field->getIdentifier().'_delete" class="checkbox" value="1" name="'.$_field->getIdentifier().'[delete]"><label for="acm_'.$_field->getIdentifier().'_delete"> '.__('Delete File (<span class="file-ex-icon file-'.end($exName).'">%1</span>)', end($fileName)).'</label><input type="hidden" value="'.$data[$_field->getIdentifier()].'" name="'.$_field->getIdentifier().'[value]"></span>';
		}
		return $arrInfo;
	}
	
	/* Store View */
	public function addStoreField($fieldset, $_field){
		$arrInfo = $this->setDefaultFieldInfo($_field);
		$arrInfo['name'] = 'stores[]';
		
		if (!$this->_storeManager->isSingleStoreMode()) {
				$arrInfo['values'] = $this->_systemStore->getStoreValuesForForm(false, true);
		}else{
			$arrInfo['values'] = $this->_storeManager->getStore(true)->getId();
		}
		return $arrInfo;
	}
	
	/* Date Field */
	public function addDateField($fieldset, $_field){
		$arrInfo = $this->setDefaultFieldInfo($_field);
		
		$dateFormat = $this->_localeDate->getDateFormat(
            \IntlDateFormatter::SHORT
        );
		
		$arrInfo['input_format'] = \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT;
		$arrInfo['date_format'] = $dateFormat;

		return $arrInfo;
	}
	
	/* Radio buttons */
	public function addCheckField($fieldset, $_field){
		$arrInfo = $this->setDefaultFieldInfo($_field);
		if($_field->getType()=='checkboxes'){
			$arrInfo['name'] = 'item['.$_field->getIdentifier().'][]';
		}

		$arrOption = [];
		$additionalInfo = json_decode($_field->getAdditionalContent(),true);
		usort($additionalInfo['option'], function($a, $b) {
			return $a['position'] - $b['position'];
		});

		foreach($additionalInfo['option'] as $key => $_option){
			$value = $_option['value'];
			if($value==''){
				$value = $_option['title'];
			}
			$arrOption[] = ['value' => $value, 'label' => $_option['title']];
		}
		$arrInfo['values'] = $arrOption;

		return $arrInfo;
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
