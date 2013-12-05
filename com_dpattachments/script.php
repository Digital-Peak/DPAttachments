<?php
/**
 * @package    DPAttachments
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2012 - 2013 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class Com_DPAttachmentsInstallerScript
{

	public function install ($parent)
	{
		$this->run("update #__extensions set enabled=1 where type = 'plugin' and element = 'dpattachments'");

		$content = 'deny from all
<Files ~ "^\w+\.(gif|jpe?g|png)$">
order deny,allow
allow from all
</Files>';

		$folder = JPATH_ROOT . '/media/com_dpattachments/attachments/';
		JFolder::create($folder);
		JFile::write($folder . '.htaccess', $content);
	}

	public function update ($parent)
	{
	}

	public function uninstall ($parent)
	{
	}

	public function preflight ($type, $parent)
	{
	}

	public function postflight ($type, $parent)
	{
	}

	private function run ($query)
	{
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$db->query();
	}
}
