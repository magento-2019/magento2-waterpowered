<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace MGS\Acm\Controller\Index;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class Post extends \Magento\Framework\App\Action\Action
{
	/**
     * @var \MGS\Acm\Model\UploadTransportBuilder
     */
    protected $_transportBuilder;
	
	/**
     * @var \MGS\Acm\Model\ResourceModel\Item\CollectionFactory
     */
	protected $_fieldFactory;
	
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
	
	/**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;
	
	protected $_filesystem;

    /**
     * File Uploader factory
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;
	
	/**
     * @var \Magento\Captcha\Helper\Data
     */
    protected $_captchaHelper;
	
	/**
     * @var CaptchaStringResolver
     */
    protected $captchaStringResolver;
	
	/**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;
	
	/**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $_acmDataHelper;
	
	public function __construct(
        \Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Element\Context $viewContext,
        \MGS\Acm\Model\UploadTransportBuilder $transportBuilder,
		\MGS\Acm\Model\ResourceModel\Field\CollectionFactory $fieldFactory,
		\Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
		\Magento\Captcha\Helper\Data $captchaHelper,
		\Magento\Captcha\Observer\CaptchaStringResolver $captchaStringResolver,
		\MGS\Acm\Helper\Data $acmDataHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
		$this->_localeDate = $viewContext->getLocaleDate();
		$this->_urlBuilder = $viewContext->getUrlBuilder();
        parent::__construct($context);
		$this->_captchaHelper = $captchaHelper;
		$this->captchaStringResolver = $captchaStringResolver;
        $this->_transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
		$this->_acmDataHelper = $acmDataHelper;
		$this->_fieldFactory = $fieldFactory;
		$this->_filesystem = $filesystem;
        $this->_fileUploaderFactory = $fileUploaderFactory;
    }
	
	/**
     * Retrieve formatting date
     *
     * @param null|string|\DateTime $date
     * @param int $format
     * @param bool $showTime
     * @param null|string $timezone
     * @return string
     */
	public function formatDate(
        $date = null,
        $format = \IntlDateFormatter::SHORT,
        $showTime = false,
        $timezone = null
    ) {
        $date = $date instanceof \DateTimeInterface ? $date : new \DateTime($date);
        return $this->_localeDate->formatDateTime(
            $date,
            $format,
            $showTime ? $format : \IntlDateFormatter::NONE,
            null,
            $timezone
        );
    }
	
	/**
     * @return void
     * @throws \Exception
     */
    public function execute()
    {
		$typeId = $this->getRequest()->getParam('type_id');
        $data = $this->getRequest()->getPostValue();
		$model = $this->_objectManager->create('MGS\Acm\Model\Acm')->load($typeId);
		
		$formId = 'acm_form';
        $captcha = $this->_captchaHelper->getCaptcha($formId);
        if ($model->getFormCaptcha()) {
            if (!$captcha->isCorrect($this->captchaStringResolver->resolve($this->getRequest(), $formId))) {
                $this->messageManager->addError(__('Incorrect CAPTCHA.'));
                $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                $this->_redirect($this->_redirect->getRefererUrl());
				return;
            }
        }
		
		
        if (!$data || !$model->getId() || !$model->getTemplateId()) {
            $this->_redirect($this->_redirect->getRefererUrl());
            return;
        }

        try {
			$formAction = $model->getFormAction();
			
			if(($formAction == 1) || ($formAction == 3)){
				$data['acm_type_id'] = $typeId;
				$data['layout'] = '1column';
				$data['url_key'] = $data['page_title'] = $data['meta_keyword'] = $data['meta_description'] = '';
				$itemModel = $this->_objectManager->create('MGS\Acm\Model\Item');
				$itemModel->setData($data);
			}
			
			$emailArr = $model->getEmail();
			$emailArr = $string = str_replace(' ', '', $emailArr);
			$emailArr = explode(',',$emailArr);
			if(
			(($formAction == 1) || ($formAction == 2)) 
			&& (count($emailArr)>0) 
			&& (!filter_var($emailArr[0], FILTER_VALIDATE_EMAIL) === false)){
				$post = $data['item'];
		
				$fields = $this->_fieldFactory->create()
					->addFieldToFilter('acm_type_id', $typeId)
					->addFieldToFilter('type', ['nin'=>['store', 'file', 'image']]);
				
				$fieldData = [];
				foreach($fields as $field){
					$fieldData[$field->getIdentifier()] = $field->getType();
				}
				
				//echo '<pre>'; print_r($post); die();
				
				foreach($fieldData as $identifier => $type){
					$postValue = $post[$identifier];
					if($type!='products'){
						$post[$identifier] = $this->changePostData($type, $postValue);
					}else{
						if($postValue!=''){
							$product = $this->_acmDataHelper->getModel('Magento\Catalog\Model\Product')->load($postValue);
							if($product->getId()){
								$post[$identifier] = $product->getName().' - '. $product->getProductUrl();
								if($formAction == 1){
									$arrProduct = [$product->getId()=>0];
									$itemModel->setProductIds($arrProduct);
								}
							}else{
								if(isset($post[$identifier.'_temp']) && ($post[$identifier.'_temp']!='')){
									$post[$identifier] = $post[$identifier.'_temp'];
								}
							}
						}else{
							if(isset($post[$identifier.'_temp']) && ($post[$identifier.'_temp']!='')){
								$post[$identifier] = $post[$identifier.'_temp'];
							}
						}
					}
				}
				
				$postObject = new \Magento\Framework\DataObject();
				$postObject->setData($post);
				
				$sender = ['name'=>$this->_acmDataHelper->getConfig('trans_email/ident_general/name'), 'email'=>$this->_acmDataHelper->getConfig('trans_email/ident_general/email')];
				$this->_transportBuilder
					->setTemplateIdentifier($model->getTemplateId())
					->setTemplateOptions(
						[
							'area' => \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
							'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
						]
					)
					->setTemplateVars(['data' => $postObject])
					->setFrom($sender)
					->addTo($emailArr[0]);
				
				if(count($emailArr)>1){
					unset($emailArr[0]);
					foreach($emailArr as $email){
						$this->_transportBuilder->addCc($email);
					}
				}
					
				
				if(
					$model->getReplyEmail()!='' 
					&& isset($data['item'][$model->getReplyEmail()]) 
					&& ($data['item'][$model->getReplyEmail()]!='') 
					&& (!filter_var($data['item'][$model->getReplyEmail()], FILTER_VALIDATE_EMAIL) === false)
				){
					$this->_transportBuilder->setReplyTo($data['item'][$model->getReplyEmail()]);
				}
				
				if($formAction == 2){
					$files = $_FILES;
					if(count($files)>0){
						foreach($files as $identifier => $file){
							if($file['name']!=''){
								
								$uploadFields = $this->_fieldFactory->create()
									->addFieldToFilter('acm_type_id', $typeId)
									->addFieldToFilter('identifier', $identifier)
									->getFirstItem();
								
								if($uploadFields->getType()=='file' || $uploadFields->getType()=='image'){
									$addPath = '';

									if($uploadFields->getAdditionalContent() != ''){
										$additionalInfo = json_decode($uploadFields->getAdditionalContent(),true);
										$extensions = explode(',',$additionalInfo['ex']);
										$addPath = $additionalInfo['path'];
										$lastChar = substr($addPath, -1);
										if($lastChar == '/'){
											$addPath = substr($addPath, 0, -1);
										}
									}
									$uploader = $this->_fileUploaderFactory->create(['fileId' => $identifier]);
									$uploader->setAllowedExtensions($extensions);
									$uploader->setAllowRenameFiles(true);
									$uploader->setFilesDispersion(true);
										
									
									$path = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($addPath);
									$uploader->save($path);
									$value = $uploader->getUploadedFileName();
									if($addPath!=''){
										$value = $addPath.$value;
									}
									$url = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath(''). $value;
									$arrName = explode('/', $value);
									$this->_transportBuilder->attachFile($url, end($arrName));
								}
							}
						}
					}
				}

				if($formAction == 1){
					$itemModel->save();
					$itemData = $itemModel->getData();
					if(isset($itemData['acm_upload_file']) && (count($itemData['acm_upload_file'])>0)){
						foreach($itemData['acm_upload_file'] as $dataKey){
							if(isset($itemData[$dataKey]) && ($itemData[$dataKey]!='')){
								$url = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath(''). $itemData[$dataKey];
								$arrName = explode('/', $itemData[$dataKey]);
								$this->_transportBuilder->attachFile($url, end($arrName));
							}
						}
					}
				}
				
				$transport = $this->_transportBuilder->getTransport();
				$transport->sendMessage();
			}
			if($formAction == 3){
				$itemModel->save();
			}
            
			if($model->getSuccessMessage()!=''){
				$this->messageManager->addSuccess(
					__($model->getSuccessMessage())
				);
			}
            
            $this->_redirect($this->_redirect->getRefererUrl());
            return;
        } catch (\Exception $e) {
            $this->messageManager->addError(
				$e->getMessage()
                //__('We can\'t process your request right now. Sorry, that\'s all we know.')
            );
            $this->_redirect($this->_redirect->getRefererUrl());
            return;
        }
    }
	
	public function changePostData($type, $value){
		switch($type){
			case 'date':
				if($value!='1970-01-01'){
					return $this->formatDate($value, \IntlDateFormatter::LONG);
				}
				return '';
			case 'checkboxes':
			case 'multiselect':
				return implode(', ',$value);
			default:
				return $value;
		}
	}
}
