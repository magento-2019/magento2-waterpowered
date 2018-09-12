<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\Acm\Helper;

/**
 * Contact base helper
 */
use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	protected $_scopeConfig;
	
	protected $_storeManager;
	
	protected $_filesystem;
	
	
	
	/**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
	
	protected $_request;
	protected $_ioFile;
	
	public function __construct(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		RequestInterface $request,
		\Magento\Framework\Filesystem\Io\File $ioFile,
		\Magento\Framework\Filesystem $filesystem,
		\Magento\Framework\ObjectManagerInterface $objectManager
	) {
		$this->_scopeConfig = $scopeConfig;
		$this->_storeManager = $storeManager;
		$this->_objectManager = $objectManager;
		$this->_request = $request;
		$this->_filesystem = $filesystem;
		$this->_ioFile = $ioFile;
	}
	
	public function getConfig($path){
		return $this->_scopeConfig->getValue($path);
	}
	
	public function getStore(){
		return $this->_storeManager->getStore();
	}
	
	public function getModel($model){
		return $this->_objectManager->create($model);
	}
	
	public function getContentTypes($type = NULL){
		$collection = $this->getModel('MGS\Acm\Model\Acm')
			->getCollection()
			->addFieldToFilter('status', 1);
		if($type){
			$collection->addFieldToFilter('content_type', $type);
		}
		
		$collection->getSelect()->where('(content_type=1) or ((content_type=2) and (form_action<>2))');
		
		if(count($collection)>0){
			foreach($collection as $key=>$contentType){
				$fields = $this->getModel('MGS\Acm\Model\Field')
					->getCollection()
					->addFieldToFilter('acm_type_id', $contentType->getId());
				if(count($fields)==0){
					$collection->removeItemByKey($key);
				}
			}
		}
		
		return $collection;
	}
	
	public function getContentData(){
		$model = $this->getModel('MGS\Acm\Model\Acm');
		$model->load($this->_request->getParam('type_id'));
		$data = $model->getData();
		unset($data['form_legend'], $data['form_note'], $data['form_action'], $data['email'], $data['template'], $data['template_detail'], $data['creation_time'], $data['update_time']);
		return $data;
	}
	
	public function getFields($typeId){
		$fields = $this->getModel('MGS\Acm\Model\Field')
			->getCollection()
			->addFieldToSelect(['title', 'identifier', 'type', 'additional_content', 'is_required', 'note'])
			->addFieldToFilter('acm_type_id', $typeId)
			->addFieldToFilter('type', ['nin'=>['store']])
			->setOrder('position', 'ASC');
		return $fields;
	}
	
	public function convertJson($str){
		$str = str_replace("'",'"',$str);
		$str = '{"filter": {'.$str.'}}';
		if($this->isJson($str)){
			$json = json_decode($str, true);
			return $json['filter'];
		}
		return;
	}

	public function isJson($string) {
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}

	public function gerateTemplateFiles($model){
		$identifier = $model->getIdentifier();
		$content = $this->getFileBaseContent($model);
		$listContent = $content['list'];
		$viewContent = $content['view'];
		
		$filePath = $this->_filesystem->getDirectoryRead(DirectoryList::APP)->getAbsolutePath('code/MGS/Acm/view/frontend/templates/advanced_content/'.$identifier.'/');
		
		$this->generateFile($filePath, 'list_base.phtml', $listContent);
		$this->generateFile($filePath, 'view_base.phtml', $viewContent);
		return;
	}
	
	public function gerateTemplateEmail($model){
		$content = $this->getTemplateContent($model);
		$template = $this->getModel('Magento\Email\Model\BackendTemplate');
		if($model->getTemplateId()!=''){
			$template->load($model->getTemplateId());
		}else{
			$template->setTemplateSubject(__('Advanced Content - %1', $model->getTitle()))
				->setTemplateCode(__('Advanced Content - %1 (%2)', $model->getTitle(), $model->getIdentifier()))
				->setOrigTemplateCode('advanced_content_'.$model->getIdentifier())
				->setTemplateType(TemplateTypesInterface::TYPE_HTML);
		}

		$template->setTemplateText($content);

		$template->save();
		return $template->getId();
	}
	
	public function getTemplateContent($model){
		/* $fileBasePath = $this->_filesystem->getDirectoryRead(DirectoryList::APP)->getAbsolutePath('code/MGS/Acm/data/');
		$io = $this->_ioFile;
		$file = $fileBasePath . '/template_email_base.html';
		$io->open(array('path' => $fileBasePath)); */
		$templateContent = '';
		//$io->streamClose();
		
		$fields = $this->getModel('MGS\Acm\Model\Field')
			->getCollection()
			->addFieldToFilter('acm_type_id', $model->getId())	
			->addFieldToFilter('type', ['nin'=>['store', 'image', 'file']])
			->setOrder('position', 'ASC');
			
		$content = '';
		if(count($fields)>0){
			foreach($fields as $field){
				$templateContent .= '<p>{{trans "'.$field->getTitle().': %'.$field->getIdentifier().'" '.$field->getIdentifier().'=$data.'.$field->getIdentifier().'}}</p>'."\n";
			}
		}
		//$templateContent = str_replace('{{template_email_content}}', $content, $templateContent);
		return $templateContent;
	}
	
	public function getFileBaseContent($model){
		$fileBasePath = $this->_filesystem->getDirectoryRead(DirectoryList::APP)->getAbsolutePath('code/MGS/Acm/data/');
		$io = $this->_ioFile;
		$file = $fileBasePath . '/list_base.phtml';
		$io->setAllowCreateFolders(true);
		$io->open(array('path' => $fileBasePath));
		$content = $io->read($file);
		$io->streamClose();
		
		$fields = $this->getModel('MGS\Acm\Model\Field')
			->getCollection()
			->addFieldToFilter('acm_type_id', $model->getId())	
			->addFieldToFilter('type', ['neq'=>'store']);
		$listContent = '';
		$viewContent = "<?php /* To get Item Information you can use ".__('$this')."->getDataInfo('identifier_of_field') */?>\n";
		if(count($fields)>0){
			foreach($fields as $field){
				$listContent .= "<div>".$field->getTitle().": <?php echo ".__('$item')."->getData('".$field->getIdentifier()."') ?></div>\n\t\t\t";
				$viewContent .= "<div>".$field->getTitle().": <?php echo ".__('$this')."->getDataInfo('".$field->getIdentifier()."') ?></div>\n";
			}
		}
		$listContent = str_replace('{{item_data}}', $listContent, $content);
		$result = ['list'=>$listContent, 'view'=>$viewContent];
		return $result;
	}
	
	public function generateFile($filePath, $fileName, $content){
		$io = $this->_ioFile;
		$file = $filePath . '/' . $fileName;
		$io->setAllowCreateFolders(true);
		$io->open(array('path' => $filePath));
		$io->write($file, $content, 0644);
		$io->streamClose();
	}
	
	public function checkInResource($rid, $resource){
		$rules = $this->getModel('Magento\Authorization\Model\Rules')
			->getCollection()
			->addFieldToFilter('role_id', $rid);
		$rules->getSelect()->where('(resource_id="Magento_Backend::all" and permission="allow") or (resource_id="'.$resource.'" and permission="allow")');
		if(count($rules)>0){
			return true;
		}
		return false;
	}
	
	public function getRoleId($userId){
		$rules = $this->getModel('Magento\Authorization\Model\Role')
			->getCollection()
			->addFieldToFilter('user_id', $userId)
			->getFirstItem();
		return $rules->getParentId();
	}
	
	public function checkIsFullPermission($userId){
		$roleId = $this->getRoleId($userId);
		
		$rules = $this->getModel('Magento\Authorization\Model\Rules')
			->getCollection()
			->addFieldToFilter('role_id', $roleId)
			->addFieldToFilter('resource_id', "Magento_Backend::all")
			->addFieldToFilter('permission', "allow");
		
		if(count($rules)>0){
			return true;
		}
		
		return false;
	}
	
	public function hasPermission($userId, $resource){
		$roleId = $this->getRoleId($userId);
		
		$rule = $this->getModel('Magento\Authorization\Model\Rules')
			->getCollection()
			->addFieldToFilter('role_id', $roleId)
			->addFieldToFilter('resource_id', $resource)
			->addFieldToFilter('permission', "allow")
			->getFirstItem();
		
		if($rule->getId()){
			return true;
		}
		
		return false;
	}
}