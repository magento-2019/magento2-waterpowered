<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Acm\Block\Adminhtml;

class Acm extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Block constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_acm';
        $this->_blockGroup = 'MGS_Acm';
        $this->_headerText = __('Content Type List');
        $this->_addButtonLabel = __('Add Content Type');
        parent::_construct();
    }

}
