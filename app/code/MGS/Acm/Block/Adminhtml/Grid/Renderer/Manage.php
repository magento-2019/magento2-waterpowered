<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Sitemap grid link column renderer
 *
 */
namespace MGS\Acm\Block\Adminhtml\Grid\Renderer;

class Manage extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
	/**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
		\Magento\Backend\Block\Context $context,
		\Magento\Backend\Model\Auth\Session $authSession,
		array $data = []
	)
    {
		$this->_authSession = $authSession;
        parent::__construct($context, $data);
    }
	
    /**
     * Prepare link to display in grid
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
		$helper =  \Magento\Framework\App\ObjectManager::getInstance()->get('MGS\Acm\Helper\Data');
		$userId = $this->_authSession->getUser()->getId();
		$contentType = $row->getContentType();
		if(($contentType==1) || (($contentType==2) && ($row->getFormAction()!=2))){
			if($helper->checkIsFullPermission($userId) || ($helper->hasPermission($userId, 'Magento_Backend::acm') && $helper->hasPermission($userId, 'MGS_Acm::'.$row->getIdentifier()))){
				return '<a href="'.$this->getUrl('adminhtml/acm/item',['type_id'=>$row->getId()]).'">'.__('Manage Items').'</a>';
			}
		}
		return false;
    }
}
