<?php
/**
 * @package    DPAttachments
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2012 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class DPAttachmentsControllerAttachments extends JControllerAdmin
{

	public function getModel ($name = 'Attachment', $prefix = 'DPAttachmentsModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}
}
