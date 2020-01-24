<?php
/**
 * @package    DPAttachments
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2012 - 2020 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\Registry\Registry;

JLoader::import('components.com_dpattachments.vendor.autoload', JPATH_ADMINISTRATOR);

// If the component is not installed we fail here and no error is thrown
if (!class_exists('\DPAttachments\Helper\Core')) {
	return;
}

class PlgContentDPAttachments extends JPlugin
{
	public function onContentAfterDisplay($context, $item, $params)
	{
		if (!isset($item->id)) {
			return '';
		}

		$catIds = $this->params->get('cat-ids');
		if (isset($item->catid) && !empty($catIds) && !in_array($item->catid, $catIds)) {
			return '';
		}

		return \DPAttachments\Helper\Core::render($context, (int)$item->id, new Registry(['render.columns' => $this->params->get('column_count', 2)]));
	}

	public function onContentAfterDelete($context, $item)
	{
		return \DPAttachments\Helper\Core::delete($context, (int)$item->id);
	}
}
