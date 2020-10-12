<?php
/**
 * @package    DPAttachments
 * @copyright  (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JHtml::_('stylesheet', 'com_dpattachments/views/attachment/image.min.css', ['relative' => true]);

$path = JComponentHelper::getParams('com_dpattachments')->get('attachment_path', 'media/com_dpattachments/attachments/');
$path = trim($path, '/') . '/' . $this->item->context . '/' . $this->item->path;
?>
<div class="com-dpattachments-attachment__content">
	<img src="<?php echo $path ?>" class="dp-attachment-image"/>
</div>
