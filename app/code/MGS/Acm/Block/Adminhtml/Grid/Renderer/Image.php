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

class Image extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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
			$url = $this->getImageUrl($value);
			$html = '<a onclick="imagePreview(\'acm_image_'.$this->getColumn()->getIndex().'\'); return false;" href="'.$url.'">';
			$html .= '<img alt="" src="'.$url.'" width="60" id="acm_image_'.$this->getColumn()->getIndex().'"/></a>';
			return $html;
		}
		return;
    }
	
	public function getImageUrl($value){
		$imageUrl = $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $value;
		return $imageUrl;
	}
}
