<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace MGS\Acm\Controller;

/**
 * Blog Controller Router
 */
class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * Event manager
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Config primary
     *
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * Url
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;
	
	/**
     * @var \MGS\Acm\Model\ResourceModel\Acm\CollectionFactory
     */
	protected $_contentFactory;
	
	/**
     * @var \MGS\Acm\Model\ResourceModel\Item\CollectionFactory
     */
	protected $_itemFactory;

    /**
     * Response
     *
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;

    /**
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magefan\Blog\Model\PostFactory $postFactory
     * @param \Magefan\Blog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ResponseInterface $response
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\UrlInterface $url,
		\MGS\Acm\Model\ResourceModel\Acm\CollectionFactory $contentFactory,
		\MGS\Acm\Model\ResourceModel\Item\CollectionFactory $itemFactory,
        \Magento\Framework\App\ResponseInterface $response
    ) {
        $this->actionFactory = $actionFactory;
        $this->_eventManager = $eventManager;
        $this->_url = $url;
        $this->_response = $response;
		$this->_contentFactory = $contentFactory;
		$this->_itemFactory = $itemFactory;
    }

    /**
     * Validate and Match Blog Pages and modify request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $identifier = trim($request->getPathInfo(), '/');
		
		$condition = new \Magento\Framework\DataObject(['identifier' => $identifier, 'continue' => true]);
        $identifier = $condition->getIdentifier();

        if ($condition->getRedirectUrl()) {
            $this->_response->setRedirect($condition->getRedirectUrl());
            $request->setDispatched(true);
            return $this->actionFactory->create('Magento\Framework\App\Action\Redirect');
        }

        if (!$condition->getContinue()) {
            return null;
        }
		
		$arrRouter = explode('/', $identifier);
		
		$success = false;
		
		if((count($arrRouter)>0) && (count($arrRouter)<3)){
			
			$contentType = $this->_contentFactory->create()
				->addFieldToFilter('identifier', $arrRouter[0])
				->addFieldToFilter('status', 1)
				->getFirstItem();
			
			if($contentType->getId()){
				if(count($arrRouter)==1){
					$request->setModuleName('acm')->setControllerName('index')->setActionName('index')->setParam('type_id', $contentType->getId());
					$success = true;
				}else{
					$item = $this->_itemFactory->create()
						->addFieldToFilter('url_key', $arrRouter[1])
						->addFieldToFilter('acm_type_id', $contentType->getId())
						->getFirstItem();
					if($item->getId()){
						$request->setModuleName('acm')
							->setControllerName('index')
							->setActionName('view')
							->setParam('type_id', $contentType->getId())
							->setParam('item_id', $item->getId());
						$success = true;
					}
				}
			}
		}
        if (!$success) {
            return null;
        }
        $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);

        return $this->actionFactory->create(
            'Magento\Framework\App\Action\Forward',
            ['request' => $request]
        );
    }

}
