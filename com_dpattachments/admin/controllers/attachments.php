<?php
/**
 * @package    DPAttachments
 * @copyright  (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class DPAttachmentsControllerAttachments extends JControllerAdmin
{

	public function getModel($name = 'Attachment', $prefix = 'DPAttachmentsModel', $config = ['ignore_request' => true])
	{
		return parent::getModel($name, $prefix, $config);
	}
}
