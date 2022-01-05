<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace DigitalPeak\Plugin\Content\DPAttachments;

defined('_JEXEC') or die;

// If the component is not installed we fail here and no error is thrown
if (!class_exists('\DigitalPeak\Component\DPAttachments\Administrator\Helper\Core')) {
	return;
}

use DigitalPeak\Component\DPAttachments\Administrator\Helper\Core;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Registry\Registry;

class DPAttachments extends CMSPlugin
{
	public function onContentAfterDisplay($context, $item, $params)
	{
		if (!isset($item->id)) {
			return '';
		}

		$catIds = $this->params->get('cat_ids');
		if (isset($item->catid) && !empty($catIds) && !in_array($item->catid, $catIds)) {
			return '';
		}

		return Core::render($context, (int)$item->id, new Registry(['render.columns' => $this->params->get('column_count', 2)]));
	}

	public function onContentAfterDelete($context, $item)
	{
		return Core::delete($context, (int)$item->id);
	}
}
