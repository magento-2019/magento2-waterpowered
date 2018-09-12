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
class Form extends Template
{

	public function getFields(){
		$helper =  \Magento\Framework\App\ObjectManager::getInstance()->get('MGS\Acm\Helper\Data');
		if($this->getData('type_id')){
			$typeId = $this->getData('type_id');
		}else{
			$typeId = $this->getRequest()->getParam('type_id');
		}
		$fields = $helper->getFields($typeId);
		return $fields;
	}
	
	public function getInputHtml($field){
		$html = '';
		$type = $field->getType();
		switch($type){
			case 'text';
				$html = $this->getTextField($field);
				break;
			case 'textarea';
				$html = $this->getTextareaField($field);
				break;
			case 'image';
				$html = $this->getUploadField($field);
				break;
			case 'file';
				$html = $this->getUploadField($field);
				break;
			case 'select';
				$html = $this->getDropdownField($field);
				break;
			case 'radios';
				$html = $this->getRadiosField($field);
				break;
			case 'checkboxes';
				$html = $this->getCheckboxField($field);
				break;
			case 'multiselect';
				$html = $this->getMultiselectField($field);
				break;
			case 'date';
				$html = $this->getDateField($field);
				break;
			case 'products';
				$html = $this->getProductField($field);
				break;
			default:
				break;
		}
		
		return $html;
	}
	
	// Generate suggest product field
	public function getProductField($field){
		$html = '<div class="acm-search-product">';
		$html .= '<input id="'.$field->getIdentifier().'_temp" data-mage-init=\'{"acmSearch":{"formSelector":"#'.$this->getContent()->getIdentifier().'-form", "url":"'.$this->getUrl('acm/index/search').'", "destinationSelector":"#'.$field->getIdentifier().'_autocomplete", "destinationText":"#'.$field->getIdentifier().'_temp", "destinationId":"#'.$field->getIdentifier().'"}}\' type="text" name="item['.$field->getIdentifier().'_temp]" placeholder="'.__('Enter product name to search').'" class="input-text" role="combobox" aria-haspopup="false" aria-autocomplete="both" autocomplete="off"';
		
		if($field->getIsRequired()){
			$html .= ' data-validate="{required:true}"';
		} 
		
		$html .= '/>';
		
		$html .= '<input id="'.$field->getIdentifier().'" type="hidden" value="" name="item['.$field->getIdentifier().']"/>';
		$html .= '<div id="'.$field->getIdentifier().'_autocomplete" class="search-autocomplete"></div>';
		$html .= '</div>';
		return $html;
	}
	
	// Generate text field
	public function getTextField($field){
		$html = '<input type="text" value="" title="'.$field->getTitle().'" id="'.$field->getIdentifier().'" name="item['.$field->getIdentifier().']"';
		if($field->getIsRequired()){
			$html .= ' data-validate="{required:true}"';
		} 
		$html .= ' class="input-text';
		if($field->getAdditionalContent() != ''){
			$additionalInfo = json_decode($field->getAdditionalContent(),true);
			$html .= ' '.implode(' ',$additionalInfo);
		}
		$html .= '"/>';
		
		return $html;
	}
	
	// Generate textarea field
	public function getTextareaField($field){
		$html = '<textarea title="'.$field->getTitle().'" id="'.$field->getIdentifier().'" name="item['.$field->getIdentifier().']"';
		if($field->getIsRequired()){
			$html .= ' data-validate="{required:true}"';
		} 
		$html .= ' class="input-text"></textarea>';
		
		return $html;
	}
	
	// Generate upload file
	public function getUploadField($field){
		$html = '<input type="file" title="'.$field->getTitle().'" id="'.$field->getIdentifier().'" name="'.$field->getIdentifier().'"';
		if($field->getIsRequired()){
			$html .= ' data-validate="{required:true}"';
		} 
		$html .= ' class="input-text"';
		
		if($field->getAdditionalContent() != ''){
			$additionalInfo = json_decode($field->getAdditionalContent(),true);
			$exAccept = explode(',',$additionalInfo['ex']);
			$exAccept = implode(',.',$exAccept);
			$html .= ' accept=".'.$exAccept.'"';
		}
		$html .= '/>';
		
		return $html;
	}
	
	// Generate radios field
	public function getRadiosField($field){
		$html = '';
		if($field->getAdditionalContent() != ''){
			$additionalInfo = json_decode($field->getAdditionalContent(),true);
			
			
			if(isset($additionalInfo['option']) && (count($additionalInfo['option'])>0)){
				usort($additionalInfo['option'], function($a, $b) {
					return $a['position'] - $b['position'];
				});
				
				$html = '<div id="'.$field->getIdentifier().'-list" class="options-list nested">';
				$i=0;
				
				foreach($additionalInfo['option'] as $option){
					$i++;
					$value = $option['value'];
					if($value==''){
						$value = $option['title'];
					}
					$html .= '<div class="field choice admin__field admin__field-option">';
					$html .= '<input type="radio" value="'.$value.'" id="options_'.$field->getIdentifier().'_'.$i.'" name="item['.$field->getIdentifier().']" class="radio admin__control-radio product-custom-option"';
					
					if($field->getIsRequired() && $i==1){
						$html .= ' checked="checked"';
					}
					$html .= '/>';
					
					$html .= '<label for="options_'.$field->getIdentifier().'_'.$i.'" class="label admin__field-label"><span>'.$option['title'].'</span> </label>';
					
					$html .= '</div>';
				}
				$html .= '<span id="'.$field->getIdentifier().'-container"></span></div>';
			}
			
		}
		
		return $html;
	}
	
	// Generate dropdown field
	public function getDropdownField($field){
		$html = '';
		if($field->getAdditionalContent() != ''){
			$additionalInfo = json_decode($field->getAdditionalContent(),true);
			
			
			if(isset($additionalInfo['option']) && (count($additionalInfo['option'])>0)){
				usort($additionalInfo['option'], function($a, $b) {
					return $a['position'] - $b['position'];
				});
				
				$html = '<select id="'.$field->getIdentifier().'" class="product-custom-option admin__control-select" title="" name="item['.$field->getIdentifier().']"';
				if($field->getIsRequired()){
					$html .= ' aria-required="true"';
				}
				$html .= '><option=""> </option>';
				foreach($additionalInfo['option'] as $option){
					$value = $option['value'];
					if($value==''){
						$value = $option['title'];
					}
					$html .= '<option value="'.$value.'">'.$option['title'].'</option>';
				}
				$html .= '</select>';
			}
			
		}
		
		return $html;
	}
	
	// Generate checkboxes field
	public function getCheckboxField($field){
		$html = '';
		if($field->getAdditionalContent() != ''){
			$additionalInfo = json_decode($field->getAdditionalContent(),true);
			
			
			if(isset($additionalInfo['option']) && (count($additionalInfo['option'])>0)){
				usort($additionalInfo['option'], function($a, $b) {
					return $a['position'] - $b['position'];
				});
				
				$html = '<div id="'.$field->getIdentifier().'-list" class="options-list nested">';
				$i=0;
				
				foreach($additionalInfo['option'] as $option){
					$i++;
					$value = $option['value'];
					if($value==''){
						$value = $option['title'];
					}
					$html .= '<div class="field choice admin__field admin__field-option">';
					$html .= '<input type="checkbox" value="'.$value.'" id="options_'.$field->getIdentifier().'_'.$i.'" name="item['.$field->getIdentifier().'][]" class="checkbox admin__control-checkbox product-custom-option"';
					
					if($field->getIsRequired()){
						$html .= ' data-validate="{required:true}"';
					}
					
					$html .= '/>';
					
					$html .= '<label for="options_'.$field->getIdentifier().'_'.$i.'" class="label admin__field-label"><span>'.$option['title'].'</span> </label>';
					
					$html .= '</div>';
				}
				$html .= '<span id="'.$field->getIdentifier().'-container"></span></div>';
			}
			
		}
		
		return $html;
	}
	
	// Generate multiselect field
	public function getMultiselectField($field){
		$html = '';
		if($field->getAdditionalContent() != ''){
			$additionalInfo = json_decode($field->getAdditionalContent(),true);
			
			
			if(isset($additionalInfo['option']) && (count($additionalInfo['option'])>0)){
				usort($additionalInfo['option'], function($a, $b) {
					return $a['position'] - $b['position'];
				});
				
				$html = '<select id="'.$field->getIdentifier().'" multiple="multiple" title="" name="item['.$field->getIdentifier().'][]" class="multiselect admin__control-multiselect product-custom-option"';
				
				if($field->getIsRequired()){
					$html .= ' data-validate="{required:true}"';
				}
				
				$html .= '>';
				foreach($additionalInfo['option'] as $option){
					$value = $option['value'];
					if($value==''){
						$value = $option['title'];
					}
					$html .= '<option value="'.$value.'">'.$option['title'].'</option>';
				}
				$html .= '</select>';
			}
			
		}
		
		return $html;
	}
	
	// Generate text field
	public function getDateField($field){
		$html = '<input type="text" name="item['.$field->getIdentifier().']" id="'.$field->getIdentifier().'" value="" class=""';

		if($field->getIsRequired()){
			$html .= ' data-validate="{required:true}"';
		}
		$html .= '/>';
		
		$html .= '<script type="text/javascript">
			require(["jquery", "mage/calendar"], function($){
                    $("#'.$field->getIdentifier().'").calendar({
                        showsTime: false,
                        dateFormat: "'.$this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT).'",
                        buttonText: "Select Date"
                    })
            });
			</script>';
		
		return $html;
	}
	
	public function getFormField($identifier){
		$fields = $this->getFields();
		
		$fields->addFieldToFilter('identifier', $identifier);
		$field = $fields->getFirstItem();
		
		$html = $this->getInputHtml($field);
		return $html;
	}
}

