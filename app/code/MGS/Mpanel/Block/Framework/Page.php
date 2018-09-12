<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mpanel\Block\Framework;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Response\HttpInterface as HttpResponseInterface;

/**
 * Main contact form block
 */
class Page extends \Magento\Framework\View\Result\Page
{
	/**
     * Add default body classes for current page layout
     *
     * @return $this
     */
    protected function addDefaultBodyClasses()
    {
        $this->pageConfig->addBodyClass($this->request->getFullActionName('-'));
        $pageLayout = $this->getPageLayout();
        if ($pageLayout) {
            $this->pageConfig->addBodyClass('page-layout-' . $pageLayout);
        }
		$width = $this->getStoreConfig('mgstheme/general/width');
		if($width != 'width1200'){
			$this->pageConfig->addBodyClass($width);
		}
		$layout = $this->getStoreConfig('mgstheme/general/layout');
		$this->pageConfig->addBodyClass($layout);
		
        return $this;
    }
	
	public function getStoreConfig($node){
		$helper =  \Magento\Framework\App\ObjectManager::getInstance()->get('MGS\Mpanel\Helper\Data');
		
		return $helper->getStoreConfig($node);
	}
	
	protected function render(HttpResponseInterface $response)
    {
        $this->pageConfig->publicBuild();
        if ($this->getPageLayout()) {
            $config = $this->getConfig();
            $this->addDefaultBodyClasses();
            $addBlock = $this->getLayout()->getBlock('head.additional'); // todo
            $requireJs = $this->getLayout()->getBlock('require.js');
			
            $this->assign([
                'requireJs' => $requireJs ? $requireJs->toHtml() : null,
                'headContent' => $this->pageConfigRenderer->renderHeadContent(),
                'headAdditional' => $addBlock ? $addBlock->toHtml() : null,
                'htmlAttributes' => $this->pageConfigRenderer->renderElementAttributes($config::ELEMENT_TYPE_HTML),
                'headAttributes' => $this->pageConfigRenderer->renderElementAttributes($config::ELEMENT_TYPE_HEAD),
                'bodyAttributes' => $this->pageConfigRenderer->renderElementAttributes($config::ELEMENT_TYPE_BODY),
                'loaderIcon' => $this->getViewFileUrl('images/loader-2.gif'),
				'topPanel' => $this->getLayout()->createBlock('MGS\Mpanel\Block\Panel\Toppanel')->setTemplate('panel/toppanel.phtml')->setCacheable(false)->toHtml(),
            ]);
			
			$output = $this->getLayout()->getOutput();
			
			$builderContent = $this->getLayout()->createBlock('MGS\Mpanel\Block\Panel\HomeContent')->setTemplate('panel/homecontent.phtml')->toHtml();

			$this->assign([
				'builderContent' => $builderContent
			]);

            $this->assign('layoutContent', $output);
            $output = $this->renderPage();
            $this->translateInline->processResponseBody($output);
            $response->appendBody($output);
        } else {
            parent::render($response);
        }
        return $this;
    }
}

