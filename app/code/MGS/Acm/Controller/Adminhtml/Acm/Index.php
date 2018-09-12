<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Acm\Controller\Adminhtml\Acm;

use Magento\Backend\App\Action;

class Index extends \MGS\Acm\Controller\Adminhtml\Acm
{
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Content Types'));
        $this->_view->renderLayout();
    }
}
