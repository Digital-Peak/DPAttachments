<?php
/**
 *
 * @package   DPAttachments
 * @copyright Copyright (C) 2016 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use Joomla\Registry\Registry;

$attachments = $displayData['attachments'] ?: [];
$options     = $displayData['options'];
if (!$options) {
	$options = new Registry();
}

JFactory::getLanguage()->load('com_dpattachments', JPATH_ADMINISTRATOR . '/components/com_dpattachments');

JHtml::_('stylesheet', 'com_dpattachments/layouts/attachments/render.min.css', ['relative' => true]);

JHtml::_('behavior.core');
JHtml::_('script', 'com_dpattachments/layouts/attachments/render.min.js', ['relative' => true], ['defer' => true]);
?>
<div class="com-dpattachments-layout-attachments<?php echo !$attachments ? ' com-dpattachments-layout-attachments_empty' : ''; ?>">
	<h4 class="com-dpattachments-layout-attachments__header"><?php echo JText::_('COM_DPATTACHMENTS_ATTACHMENTS'); ?></h4>
	<div class="com-dpattachments-layout-attachments__attachments" data-context="<?php echo $displayData['context']; ?>"
		 data-item="<?php echo $displayData['itemid']; ?>">
		<?php foreach ($attachments as $attachment) { ?>
			<?php echo \DPAttachments\Helper\Core::renderLayout('attachment.render', ['attachment' => $attachment]); ?>
		<?php } ?>
	</div>
</div>