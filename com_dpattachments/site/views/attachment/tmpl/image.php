<?php
/**
 * @package		DPAttachments
 * @author		Digital Peak http://www.digital-peak.com
 * @copyright	Copyright (C) 2012 - 2013 Digital Peak. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$input = JFactory::getApplication()->input;

$path = JComponentHelper::getParams('com_dpattachments')->get('attachment_path', 'media/com_dpattachments/attachments/');
$path = trim($path, '/') . '/' . $this->item->context . '/' . $this->item->path;

if ($input->get('tmpl') != 'component') {
    ?>
<div class="page-header">
	<h2><?php echo $this->escape($this->item->title); ?></h2>
</div>
<?php
}?>

<img src="<?php echo $path?>"></img>