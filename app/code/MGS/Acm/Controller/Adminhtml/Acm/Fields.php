<?php
/**
 * Get related products grid and serializer block
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Acm\Controller\Adminhtml\Acm;

class Fields extends \MGS\Acm\Controller\Adminhtml\Acm
{
    
    /**
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
		$layout = $this->_view->getLayout();
		$html = $layout->createBlock('MGS\Acm\Block\Adminhtml\Edit\Tab\Fields')->toHtml();
		echo $html;
    }
}
