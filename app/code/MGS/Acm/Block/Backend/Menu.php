<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Acm\Block\Backend;

/**
 * Main contact form block
 */
class Menu extends \Magento\Backend\Block\Menu
{
	public function renderNavigation($menu, $level = 0, $limit = 0, $colBrakes = [])
    {
        $itemPosition = 1;
        $outputStart = '<ul ' . (0 == $level ? 'id="nav" role="menubar"' : 'role="menu"') . ' >';
        $output = '';
		
		$_check = false;

        /** @var $menuItem \Magento\Backend\Model\Menu\Item  */
		$i=0;
		
		$menuIterotor = $this->_getMenuIterator($menu);
        foreach ($menuIterotor as $menuItem) {
			$i++;
            $menuId = $menuItem->getId();
            $itemName = substr($menuId, strrpos($menuId, '::') + 2);
            $itemClass = str_replace('_', '-', strtolower($itemName));

            if (count($colBrakes) && $colBrakes[$itemPosition]['colbrake']) {
                $output .= '</ul></li><li class="column"><ul role="menu">';
            }

            $id = $this->getJsId($menuItem->getId());
            $subMenu = $this->_addSubMenu($menuItem, $level, $limit, $id);
            $anchor = $this->_renderAnchor($menuItem, $level);
            
			$helper =  \Magento\Framework\App\ObjectManager::getInstance()->get('MGS\Acm\Helper\Data');
			$userId = $this->_authSession->getUser()->getId();
			
			// If full permission
			if($helper->checkIsFullPermission($userId)){
				
				// If has menu MGS
				if($id == 'menu-magento-backend-mgs'){
					$output .= '<li ' . $this->getUiId($menuItem->getId())
					. ' class="item-' . $itemClass . ' ' . $this->_renderItemCssClass($menuItem, $level)
					. ($level == 0 ? '" id="' . $id . '" aria-haspopup="true' : '')
					. '" role="menu-item">' . $anchor . $subMenu . '</li>';
					
					
					$contentTypes = $helper->getContentTypes();
					$_collectionSize = count($contentTypes);
					if($_collectionSize>0){
						$acmLabel = __('ACM');
						if($_collectionSize>4){
							$acmLabel = __('Advanced Content');
						}
						$liClass = 'item-acm parent level-0';
						$controllerName = $this->getRequest()->getControllerName();
						$actionName = $this->getRequest()->getActionName();
						if(($controllerName=='acm') &&
						(($actionName=='item') || 
						($actionName=='newitem') || 
						($actionName=='edititem'))){
							$liClass .= ' _active';
						}
						$output .= '<li id="menu-magento-backend-mgs-acm" class="'.$liClass.'" role="menu-item" aria-haspopup="true" data-ui-id="menu-magento-backend-mgs-acm">
							<a class="" onclick="return false;" href="#"><span>' . __('ACM') . '</span></a>';
						
						$output .= '<div class="submenu" aria-labelledby="menu-magento-backend-mgs-acm" aria-expanded="true">
							<strong class="submenu-title">' . $acmLabel . '</strong>
							<a class="action-close _close" data-role="close-submenu" href="#"></a>';
						
						
						
						$output .= '<ul role="menu">';
						
						
						$_columnCount = 4;
						
						$i=0;
						foreach($contentTypes as $type){
							if ($i++%$_columnCount==0){
								$output .= '<li class="column"><ul role="menu">';
							}
							
							$output .= '<li role="menu-item" class="item-content-'.$type->getIdentifier().'  parent  level-1" data-ui-id="menu-magento-backend-content-'.$type->getIdentifier().'">
								<strong role="presentation" class="submenu-group-title"><span>'.$type->getTitle().'</span></strong>
								<div class="submenu">
									<ul role="menu">
										<li role="menu-item" class="item-manager level-2" data-ui-id="menu-mgs-acm-type-'.$type->getIdentifier().'-new">
											<a class="" href="'.$this->getUrl('adminhtml/acm/item', ['type_id'=>$type->getId()]).'"><span>'. __('Manage %1', $type->getTitle()).'</span></a>
										</li>';
							if($type->getContentType()==1){
								$output .= '<li role="menu-item" class="item-add level-2" data-ui-id="menu-mgs-acm-type-'.$type->getIdentifier().'-add">
											<a class="" href="'.$this->getUrl('adminhtml/acm/newitem', ['type_id'=>$type->getId()]).'"><span>'. __('Add new %1', $type->getTitle()).'</span></a>
										</li>';
							}
							
										
							$output .= '</ul>
								</div>
							</li>';
							if ($i%$_columnCount==0 || $i==$_collectionSize){
								$output .= '</ul></li>';
							}
						}
						
						$output .= '</ul>';
							
						$output .= '</div>';
						
						$output .= '</li>';
						
					}
					
					
				}else{
					$output .= '<li ' . $this->getUiId($menuItem->getId())
					. ' class="item-' . $itemClass . ' ' . $this->_renderItemCssClass($menuItem, $level)
					. ($level == 0 ? '" id="' . $id . '" aria-haspopup="true' : '')
					. '" role="menu-item">' . $anchor . $subMenu . '</li>';
				}
			}else{
				
				// If has permission to access ACM menu (Not full permission)
				if($helper->hasPermission($userId, 'Magento_Backend::acm')){
					$_check = true;
				}
				
				$output .= '<li ' . $this->getUiId($menuItem->getId())
					. ' class="item-' . $itemClass . ' ' . $this->_renderItemCssClass($menuItem, $level)
					. ($level == 0 ? '" id="' . $id . '" aria-haspopup="true' : '')
					. '" role="menu-item">' . $anchor . $subMenu . '</li>';
			}
			
            $itemPosition++;
        }
		
		


        if (count($colBrakes) && $limit) {
            $output = '<li class="column"><ul role="menu">' . $output . '</ul></li>';
        }
		
		
		
		$html = $outputStart . $output;
		
		if($_check && (0 == $level)){
			$contentTypes = $helper->getContentTypes();
			
			foreach($contentTypes as $typeKey=>$type){
				
				if(!$helper->hasPermission($userId, 'MGS_Acm::'.$type->getIdentifier())){
					$contentTypes->removeItemByKey($typeKey);
				}
			}
			
			$_collectionSize = count($contentTypes);
			
			if($_collectionSize>0){
				$acmLabel = __('ACM');
				if($_collectionSize>4){
					$acmLabel = __('Advanced Content');
				}
				$liClass = 'item-acm parent level-0';
				$controllerName = $this->getRequest()->getControllerName();
				$actionName = $this->getRequest()->getActionName();
				if(($controllerName=='acm') &&
				(($actionName=='item') || 
				($actionName=='newitem') || 
				($actionName=='edititem'))){
					$liClass .= ' _active';
				}
				
				$html .= '<li id="menu-magento-backend-mgs-acm" class="'.$liClass.'" role="menu-item" aria-haspopup="true" data-ui-id="menu-magento-backend-mgs-acm">
					<a class="" onclick="return false;" href="#"><span>' . __('ACM') . '</span></a>';
				
				$html .= '<div class="submenu" aria-labelledby="menu-magento-backend-mgs-acm" aria-expanded="true">
					<strong class="submenu-title">' . $acmLabel . '</strong>
					<a class="action-close _close" data-role="close-submenu" href="#"></a>';
				
				
				
				$html .= '<ul role="menu">';
				
				
				$_columnCount = 4;
				
				$j=0;
				foreach($contentTypes as $type){

					if ($j++%$_columnCount==0){
						$html .= '<li class="column"><ul role="menu">';
					}
					
					$html .= '<li role="menu-item" class="item-content-'.$type->getIdentifier().'  parent  level-1" data-ui-id="menu-magento-backend-content-'.$type->getIdentifier().'">
						<strong role="presentation" class="submenu-group-title"><span>'.$type->getTitle().'</span></strong>
						<div class="submenu">
							<ul role="menu">
								<li role="menu-item" class="item-manager level-2" data-ui-id="menu-mgs-acm-type-'.$type->getIdentifier().'-new">
									<a class="" href="'.$this->getUrl('adminhtml/acm/item', ['type_id'=>$type->getId()]).'"><span>'. __('Manage %1', $type->getTitle()).'</span></a>
								</li>';
					if($type->getContentType()==1){
						$html .= '<li role="menu-item" class="item-add level-2" data-ui-id="menu-mgs-acm-type-'.$type->getIdentifier().'-add">
									<a class="" href="'.$this->getUrl('adminhtml/acm/newitem', ['type_id'=>$type->getId()]).'"><span>'. __('Add new %1', $type->getTitle()).'</span></a>
								</li>';
					}
					
								
					$html .= '</ul>
						</div>
					</li>';
					
					if ($j%$_columnCount==0 || $j==$_collectionSize){
						$html .= '</ul></li>';
					}

				}
				
				$html .= '</ul>';
					
				$html .= '</div>';
				
				$html .= '</li>';
				
			}
		}

        return  $html . '</ul>';
    }
}

