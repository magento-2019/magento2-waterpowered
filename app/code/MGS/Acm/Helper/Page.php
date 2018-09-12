<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Acm\Helper;

/**
 * CMS Page Helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class Page extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Return result CMS page
     *
     * @param Action $action
     * @param null $pageId
     * @return \Magento\Framework\View\Result\Page|bool
     */
    public function prepareResultPage( $resultPage, $model = null)
    {
		$resultPage->getConfig()->setPageLayout($model->getLayout());
        
        $layoutUpdate = $model->getLayoutUpdateXml();
		
        if (!empty($layoutUpdate)) {
            $resultPage->getLayout()->getUpdate()->addUpdate($layoutUpdate);
        }

        return $resultPage;
    }
}
