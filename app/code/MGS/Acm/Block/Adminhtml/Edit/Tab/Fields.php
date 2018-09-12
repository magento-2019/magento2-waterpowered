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
class Fields extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
	protected $_objectManager;
	protected $_addRowButtonHtml = array();
    protected $_removeRowButtonHtml = array();
	
    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\View\Helper\Js $jsHelper
     * @param array $data
     */
	 
	public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
		\Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
		$this->_objectManager = $objectManager;
        parent::__construct($context, $registry, $formFactory, $data);
    }
	
	public function getModel(){
		return $this->_objectManager->create('MGS\Acm\Model\Field');
	}
	
	public function getAcmContentType(){
		$id = $this->getRequest()->getParam('id');
		return $this->_objectManager->create('MGS\Acm\Model\Acm')->load($id);
	}
	
	/**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('MGS_Acm::fields.phtml');
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
	
	public function getFieldHtml($type){
		return $this->getLayout()->createBlock('MGS\Acm\Block\Adminhtml\Edit\Tab\Fields\Element')->setTemplate('MGS_Acm::fields/'.$type.'.phtml');
	}
	
	public function getLastId(){
		$lastField  = $this->getModel()->getCollection()->setOrder('field_id', 'DESC')->getFirstItem();
		if($lastField->getId()){
			return $lastField->getId();
		}
		else{
			return 0;
		}
		
	}
	
	public function getFieldsForm(){
		$model  = $this->getModel()->getCollection()->setOrder('position', 'ASC');
		if($id = $this->getRequest()->getParam('id')){
			$model->addFieldToFilter('acm_type_id', $id);
		}
		$fields = [];
		$i=1;
		foreach ($model as $field) {
			$fields[$i++] = $field;
		}
		return $fields;
		
	}
	
	public function getHtmlFields()
    {
        $html = '<ul id="field-template" style="display:none">';
        $html .= $this->getRowTemplateHtml();
        $html .= '</ul>';

        $html .= '<ul id="field-items">';
        if (count($this->getFieldsForm())) {
            foreach ($this->getFieldsForm() as $field) {
                $html .= $this->getRowTemplateHtml($field->getId());
            }
        }
        $html .= '</ul>';
        $html .= '<div class="button-container"><button class="action-default scalable add primary" type="button" id="add-new-field"><span>'.__('Add New Field').'</span></button></div>';
        return $html;
    }
	
	public function getRowTemplateHtml($i=0)
    {	
		$j = $i;
		if($i==0){
			$i = '{{number}}';
		}
		$html = '<li class="ui-state-default">';
		$html .= '<div id="grid_tab'.$i.'" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
			<ul class="tabs-horiz ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all" role="tablist">
				<li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active" role="tab" tabindex="0" aria-controls="grid_tab_main_field_content'.$i.'" aria-labelledby="grid_tab_main_field'.$i.'" aria-selected="true">
					<a data-tab-type="" class="ui-tabs-anchor" title="Field Information" id="grid_tab_main_field'.$i.'" href="#grid_tab_main_field_content'.$i.'" role="presentation" tabindex="-1">
						<span>'.__('Field Information').'</span>
					</a>
				</li>
				<li class="ui-state-default ui-corner-top" role="tab" tabindex="-1" aria-controls="grid_tab_additional_content'.$i.'" aria-labelledby="grid_tab_additional" aria-selected="false">
					<a data-tab-type="" class="ui-tabs-anchor" title="Additional Information" id="grid_tab_additional" href="#grid_tab_additional_content'.$i.'" role="presentation" tabindex="-1">
						<span>'.__('Additional Information').'</span>
					</a>
				</li>
			</ul>
			<button id="remove-field" type="button" class="action-default scalable add primary" onclick="removeField('.$i.')"><span>Remove</span></button>
		</div>
		<div class="dashboard-store-stats-content" id="grid_tab_content'.$i.'">
			<div style="display: block; padding-left:0; padding-right:0" id="grid_tab_main_field_content'.$i.'" aria-labelledby="grid_tab_main_field" class="ui-tabs-panel ui-widget-content ui-corner-bottom" role="tabpanel" aria-expanded="true" aria-hidden="false">
				<div class="dashboard-item-content">';
		
		$html .= '<div>
			<table class="data-grid">
				<tr>
					<td rowspan="2" class="col-draggable"><div class="draggable-handle" title="'.__('Sort Field').'"></div></td>
					<th class="data-grid-th col-title"><span>'.__('Title').'</span></th>
					<th class="data-grid-th col-identifier"><span>'.__('Identifier').'</span></th>
					<th class="data-grid-th col-type"><span>'.__('Input Type').'</span></th>
					<th class="data-grid-th col-required"><span>'.__('Is Required').'</span></th>
					<th class="data-grid-th con-show-grid"><span>'.__('Show In Grid').'</span></th>
					<th class="data-grid-th col-note"><span>'.__('Position').'</span></th>
				</tr>
				<tr class="data-grid-filters">';

		$html .= $this->getTitle($j);
		$html .= $this->getIdentifier($j);
		$html .= $this->getInputType($j);
		$html .= $this->getRequire($j);
		$html .= $this->getInGrid($j);
		$html .= $this->getPosition($j);
		
		$html .= '</tr>
			</table>
		</div>';
					
		$html .= '</div>
			</div>
			<div id="grid_tab_additional_content'.$i.'" class="ui-tabs-panel ui-widget-content ui-corner-bottom" aria-live="polite" aria-labelledby="grid_tab_additional" role="tabpanel" style="display: none;" aria-expanded="false" aria-hidden="true">
				<div class="additional-item-content" id="additional-item-content'.$i.'">';
		if($i!=0){
			$field 	= $this->getModel()->load($j);
			$html .= $this->getAdditionalInformationHtml($field->getType(), $j);
		}
					
		$html .= '</div>
			</div>
			<hr/>
		</div>';
		
		if($i!=0){
			$html .= '<input type="hidden" value="" id="remove'.$i.'" name="field[remove][]"/>';
		}
		
		$html .= '</li>';
        
        return $html;
    }
	
	public function getTitle($j=0){
		$field 	= $this->getModel()->load($j);
		if($j!=0){
			$html = '<td><input type="hidden" name="field['.$j.'][field_id]" value="'.$j.'"/><input type="text" class="input-text admin__control-text required-entry" value="'.$field->getTitle().'" name="field['.$j.'][title]"';
		}else{
			$html = '<td><input type="hidden" name="field[{{number}}][field_id]" value=""/><input type="text" class="input-text admin__control-text input-field-title required-entry" value="" name="field[{{number}}][title]"';
		}
		
		$html .= '/></td>';
		return $html;
	}
	
	public function getIdentifier($j=0){
		$field 	= $this->getModel()->load($j);
		
		if($j!=0){
			$html = '<td><input readonly="readonly" type="text" class="input-text admin__control-text" value="'.$field->getIdentifier().'" name="field['.$j.'][identifier]"';
		}else{
			$html = '<td><input readonly="readonly" type="text" class="input-text admin__control-text input-field-identifier" value="" name="field[{{number}}][identifier]"';
		}
		$html .= '/></td>';
		return $html;
	}
	
	public function getInputType($j=0){
		$field 	= $this->getModel()->load($j);
		$type = $field->getType();
		
		if($j!=0){
			$html = '<td><select class="no-changes admin__control-select required-entry" name="field['.$j.'][type]" readonly="readonly"';
		}else{
			$html = '<td><select class="no-changes admin__control-select required-entry" name="field[{{number}}][type]" onchange="setAdditionalInfo(this.value,{{number}})"';
		}

		$html .= '><option value=""></option>';
		$html .= '<option value="text"';
		if($type == 'text'){
			$html .= ' selected="selected"';
		}
		$html .= '>'.__('Text Field').'</option>';
		
		$html .= '<option value="textarea"';
		if($type == 'textarea'){
			$html .= ' selected="selected"';
		}
		$html .= '>'.__('Text Area').'</option>';
		
		$html .= '<option value="file"';
		if($type == 'file'){
			$html .= ' selected="selected"';
		}
		$html .= '>'.__('File').'</option>';
		
		$html .= '<option value="image"';
		if($type == 'image'){
			$html .= ' selected="selected"';
		}
		$html .= '>'.__('Image').'</option>';
		
		$html .= '<option value="select"';
		if($type == 'select'){
			$html .= ' selected="selected"';
		}
		$html .= '>'.__('Dropdown').'</option>';
		
		$html .= '<option value="radios"';
		if($type == 'radios'){
			$html .= ' selected="selected"';
		}
		$html .= '>'.__('Radio').'</option>';
		
		$html .= '<option value="checkboxes"';
		if($type == 'checkboxes'){
			$html .= ' selected="selected"';
		}
		$html .= '>'.__('Checkbox').'</option>';
		
		$html .= '<option value="multiselect"';
		if($type == 'multiselect'){
			$html .= ' selected="selected"';
		}
		$html .= '>'.__('Multiselect').'</option>';
		
		$html .= '<option value="date"';
		if($type == 'date'){
			$html .= ' selected="selected"';
		}
		$html .= '>'.__('Date').'</option>';
		
		$html .= '<option value="store"';
		if($type == 'store'){
			$html .= ' selected="selected"';
		}
		$html .= '>'.__('Store View').'</option>';
		
		$html .= '<option value="products"';
		if($type == 'products'){
			$html .= ' selected="selected"';
		}
		$html .= '>'.__('Products').'</option>';
		
		$html .= '</select></td>';
		return $html;
	}
	
	public function getRequire($j=0){
		$field 	= $this->getModel()->load($j);
		$require = $field->getIsRequired();
		
		if($j!=0){
			$html = '<td><select class="no-changes admin__control-select" name="field['.$j.'][is_required]"';
		}else{
			$html = '<td><select class="no-changes admin__control-select" name="field[{{number}}][is_required]"';
		}
		
		
		$html .= '><option value="0">'.__('No').'</option>';
		
		$html .= '<option value="1"';
		if($require == 1){
			$html .= ' selected="selected"';
		}
		$html .= '>'.__('Yes').'</option></select></td>';
		return $html;
	}
	
	public function getInGrid($j=0){
		$field 	= $this->getModel()->load($j);
		$grid = $field->getInGrid();
		
		if($j!=0){
			$html = '<td><select class="no-changes admin__control-select" name="field['.$j.'][in_grid]"';
		}else{
			$html = '<td><select class="no-changes admin__control-select" name="field[{{number}}][in_grid]"';
			$grid = 1;
		}
		
		
		$html .= '>';
		
		
		
		$html .= '<option value="1">'.__('Yes').'</option>';
		
		$html .= '<option value="0"';
		
		if($grid == 0){
			$html .= ' selected="selected"';
		}
		
		$html .= '>'.__('No').'</option></select></td>';
		
		return $html;
	}
	
	public function getPosition($j=0){
		$field 	= $this->getModel()->load($j);
		
		if($j!=0){
			$html = '<td><input type="text" class="input-text admin__control-text input-position" value="'.$field->getPosition().'" name="field['.$j.'][position]"';
		}else{
			$html = '<td><input type="text" class="input-text admin__control-text input-position" value="" name="field[{{number}}][position]"';
		}
		
		$html .= '></td>';
		return $html;
	}
	
	public function getAdditionalInformationHtml($type, $i=0){
		$html = '';
		switch($type){
			case 'text':
				$html = $this->getTextAdditionalInformation($i);
				break;
			case 'textarea':
				$html = $this->getTextAreaAdditionalInformation($i);
				break;
			case 'file':
				$html = $this->getFileAdditionalInformation($i, 'file');
				break;
			case 'image':
				$html = $this->getFileAdditionalInformation($i, 'image');
				break;
			case 'date':
				$html = $this->getNullAdditionalInformation($i);
				break;
			case 'store':
				$html = $this->getNullAdditionalInformation($i);
				break;
			case 'products':
				$html = '';
				break;
			default:
				$html = $this->getDropdownAdditionalInformation($i);
				break;
		}
		return $html;
	}
	
	public function getTextAdditionalInformation($i){
		$field 	= $this->getModel()->load($i);
		
		$html = '<table class="data-grid"><tr><th class="data-grid-th col-note"><span>'.__('Note').'</span></th><th class="data-grid-th col-type"><span>'.__('Validate').'</span></th></tr><tr><td>';
				
		if($i!=0){
			$html .= '<input type="text" class="input-text admin__control-text" value="'.$field->getNote().'" name="field['.$i.'][note]" />';
		}else{
			$html .= '<input type="text" class="input-text admin__control-text" value="" name="field[{{number}}][note]" />';
		}
				
		$html .= '</td><td>';
		
		$additionalInfo = [];
		if($field->getAdditionalContent() != ''){
			$additionalInfo = json_decode($field->getAdditionalContent(),true);
		}
		$requireClass = [
			'validate-number' => __('Number Only'), 
			'validate-alpha'=> __('Letter Only'), 
			'validate-alphanum'=> __('Number Or Letter'), 
			'validate-email'=>__('Email'),
			'validate-url'=>__('Url'),
			'validate-identifier'=>__('Identifier'),
			'validate-zero-or-greater'=>__('0 Or Greater'),
			'validate-greater-than-zero'=>__('Greater Than 0'),
		];
		
		$html .= '<select multiple="multiple" size="8" class="select multiselect admin__control-multiselect"';
		if($i!=0){
			$html .= ' name="field['.$i.'][additional_content][]">';
			
		}else{
			$html .= ' name="field[{{number}}][additional_content][]">';
		}
		
		foreach($requireClass as $value=>$label){
			$html .= '<option value="'.$value.'"';
			if(in_array($value,$additionalInfo)){
				$html .= ' selected="selected"';
			}
			$html .= '>'.$label.'</option>';
		}
		
		$html .= '</select>';
		
		
		$html .= '</td></tr></table>';
		return $html;
	}
	
	public function getTextAreaAdditionalInformation($i){
		$field 	= $this->getModel()->load($i);
		
		$html = '<table class="data-grid"><tr><th class="data-grid-th col-note"><span>'.__('Note').'</span></th><th class="data-grid-th col-type"><span>'.__('Enable WYSIWYG').'</span></th></tr><tr><td>';
				
		if($i!=0){
			$html .= '<input type="text" class="input-text admin__control-text" value="'.$field->getNote().'" name="field['.$i.'][note]" />';
		}else{
			$html .= '<input type="text" class="input-text admin__control-text" value="" name="field[{{number}}][note]" />';
		}
				
		$html .= '</td><td>';
		
		$additionalInfo = json_decode($field->getAdditionalContent(),true);
		
		$html .= '<select class="select admin__control-select"';
		if($i!=0){
			$html .= ' name="field['.$i.'][additional_content]">';
			
		}else{
			$html .= ' name="field[{{number}}][additional_content]">';
		}
		

		$html .= '<option value="0">'.__('No').'</option>';
		$html .= '<option value="1"';
		if($additionalInfo==1){
			$html .= ' selected="selected"';
		}
		$html .= '>'.__('Yes').'</option>';

		
		$html .= '</select>';
		
		
		$html .= '</td></tr></table>';
		return $html;
	}
	
	public function getFileAdditionalInformation($i, $type){
		$field 	= $this->getModel()->load($i);
		
		if($type=='file'){
			$ex = 'doc,docx,pdf,odt,xls,xlsx,csv';
		}else{
			$ex = 'png,jpg,jpeg,gif';
		}
		
		$html = '<table class="data-grid"><tr><th class="data-grid-th col-note"><span>'.__('Note').'</span></th><th class="data-grid-th col-type"><span>'.__('Allowed File Extensions').'</span></th><th class="data-grid-th col-type"><span>'.__('File path').'</span></th></tr><tr><td>';
				
		if($i!=0){
			$html .= '<input type="text" class="input-text admin__control-text" value="'.$field->getNote().'" name="field['.$i.'][note]" />';
		}else{
			$html .= '<input type="text" class="input-text admin__control-text" value="" name="field[{{number}}][note]" />';
		}
				
		$html .= '</td><td>';
		
		$additionalInfo = json_decode($field->getAdditionalContent(),true);
		

		if($i!=0){
			$html .= '<input type="text" class="input-text admin__control-text required-entry" value="'.$additionalInfo['ex'].'" name="field['.$i.'][additional_content][ex]" />';
			
		}else{
			$html .= '<input type="text" class="input-text admin__control-text required-entry" value="'.$ex.'" name="field[{{number}}][additional_content][ex]" />';
		}
		
		$html .= '<span class="note">'.__('Comma separated list').'</span></td><td>';
		
		if($i!=0){
			$html .= '<input type="text" class="input-text admin__control-text" value="'.$additionalInfo['path'].'" name="field['.$i.'][additional_content][path]" />';
			
		}else{
			$html .= '<input type="text" class="input-text admin__control-text" value="" name="field[{{number}}][additional_content][path]" />';
		}
		
		
		
		$html .= '<span class="note">'.__('Sub-directly to store files.').'</span></td></tr></table>';
		return $html;
	}
	
	public function getDropdownAdditionalInformation($i){
		$field 	= $this->getModel()->load($i);
		
		$html = '<table class="data-grid"><tr><th class="data-grid-th col-note"><span>'.__('Note').'</span></th><th class="data-grid-th col-type"><span>'.__('Manage Options').'</span></th></tr><tr><td>';
				
		if($i!=0){
			$html .= '<input type="text" class="input-text admin__control-text" value="'.$field->getNote().'" name="field['.$i.'][note]" />';
		}else{
			$html .= '<input type="text" class="input-text admin__control-text" value="" name="field[{{number}}][note]" />';
		}
				
		$html .= '</td><td>';
		
		
		
		$html .= '<table class="admin__control-table"><thead><tr><th>'.__('Title').'</th><th>'.__('Value').'</th><th style="width:20%">'.__('Position').'</th><th></th></tr></thead>';
		
		if($i!=0){
			$html .= '<tbody id="option-table'.$i.'">';
		}else{
			$html .= '<tbody id="option-table{{number}}">';
		}
		
		
		
		$additionalInfo = [];
		if($field->getAdditionalContent()!=''){
			$additionalInfo = json_decode($field->getAdditionalContent(),true);
		}
		
		if(isset($additionalInfo['option']) && (count($additionalInfo['option'])>0)){
			usort($additionalInfo['option'], function($a, $b) {
				return $a['position'] - $b['position'];
			});
			foreach($additionalInfo['option'] as $key => $_option){
				$html .= '<tr class="row-option"><td><input type="text" name="field['.$i.'][additional_content][option]['.$key.'][title]" value="'.$_option['title'].'" class="input-text required-entry"/></td><td><input type="text" name="field['.$i.'][additional_content][option]['.$key.'][value]" value="'.$_option['value'].'" class="input-text"/></td><td><input type="text" name="field['.$i.'][additional_content][option]['.$key.'][position]" value="'.$_option['position'].'" class="input-text"/></td><td><button class="action- scalable delete delete-option" type="button" title="Delete" onclick="removeOption(this)"><span>' . __('Delete') . '</span></button></td></tr>';
			}
		}
		
		
		$html .= '</tbody><tfoot><tr><th class="col-actions-add" colspan="4">';
		
		if($i!=0){
			$html .= '<button class="action- scalable add" type="button" title="'.__('Add Option').'"  onclick="addNewOption('.$i.')"><span>'.__('Add Option').'</span></button>';
		}else{
			$html .= '<button class="action- scalable add" type="button" title="'.__('Add Option').'"  onclick="addNewOption({{number}})"><span>'.__('Add Option').'</span></button>';
		}
			
		
		$html .= '</th></tr></tfoot></table>';
		
		if(($i!=0) && isset($additionalInfo['option']) && (count($additionalInfo['option'])>0)){
			$html .= '<input type="hidden" value="'.count($additionalInfo['option']).'" id="input-hidden'.$i.'"/>';
		}else{
			$html .= '<input type="hidden" value="0" id="input-hidden{{number}}"/>';
		}
		
		
		$html .= '</td></tr></table>';
		return $html;
	}
	
	public function getNullAdditionalInformation($i){
		$field 	= $this->getModel()->load($i);
		
		$html = '<table class="data-grid"><tr><th class="data-grid-th col-note"><span>'.__('Note').'</span></th></tr><tr><td>';
				
		if($i!=0){
			$html .= '<input type="text" class="input-text admin__control-text" value="'.$field->getNote().'" name="field['.$i.'][note]" />';
		}else{
			$html .= '<input type="text" class="input-text admin__control-text" value="" name="field[{{number}}][note]" />';
		}

		$html .= '</td></tr></table>';
		return $html;
	}
}
