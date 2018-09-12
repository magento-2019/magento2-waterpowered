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

class File extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Prepare link to display in grid
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
		$value = $this->_getValue($row);
		if($value!=''){
			$fileName = explode('/',$value);
			$exName = explode('.', end($fileName));
			$html = '<span class="file-ex-icon file-'.end($exName).'">'.end($fileName).'</span>';
			return $html;
		}
		return;
    }
}
