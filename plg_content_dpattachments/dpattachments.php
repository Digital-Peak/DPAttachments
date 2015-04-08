<?php
/**
 * @package    DPAttachments
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2012 - 2015 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('components.com_dpattachments.libraries.dpattachments.core', JPATH_ADMINISTRATOR);

// If the component is not installed we fail here and no error is thrown
if (! class_exists('DPAttachmentsCore'))
{
	return;
}

class PlgContentDpattachments extends JPlugin
{

	public function onContentAfterDisplay ($context, $item, $params)
	{
		if (! isset($item->id))
		{
			return '';
		}

		$catIds = $this->params->get('cat-ids');
		if (isset($item->catid) && ! empty($catIds) && ! in_array($item->catid, $catIds))
		{
			return '';
		}

		$options = new JRegistry();
		$options->set('render.columns', $this->params->get('column_count', 2));
		return DPAttachmentsCore::render($context, (int) $item->id, $options);
	}

	public function onContentAfterDelete ($context, $item)
	{
		return DPAttachmentsCore::delete($context, (int) $item->id);
	}
}
