<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace DigitalPeak\Component\DPAttachments\Site\Dispatcher;

use Joomla\CMS\Dispatcher\ComponentDispatcher;

class Dispatcher extends ComponentDispatcher
{
	public function dispatch(): void
	{
		$this->getApplication()->getLanguage()->load('com_dpattachments', JPATH_ADMINISTRATOR . '/components/com_dpattachments');
		parent::dispatch();
	}
}
