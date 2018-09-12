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

use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;
class Date extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Date
{
    /**
     * Prepare link to display in grid
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
		$timezone = $this->getColumn()->getTimezone() !== false ? $this->_localeDate->getConfigTimezone() : 'UTC';
		if($this->_getValue($row) != '1970-01-01'){
			return $this->dateTimeFormatter->formatObject(
				$this->_localeDate->date(
					new \DateTime(
						$this->_getValue($row),
						new \DateTimeZone($timezone)
					)
				),
				$this->_getFormat()
			);
		}else{
			return;
		}
    }

}
