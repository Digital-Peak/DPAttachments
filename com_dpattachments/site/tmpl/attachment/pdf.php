<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

\defined('_JEXEC') or die();

use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('stylesheet', 'com_dpattachments/dpattachments/views/attachment/pdf.min.css', ['relative' => true]);

$path = $this->params->get('attachment_path', 'media/com_dpattachments/attachments/');
$path = trim((string) $path, '/') . '/' . $this->item->context . '/' . $this->item->path;
?>
<div class="com-dpattachments-attachment__content">
	<embed src="<?php echo $path ?>" type="application/pdf" frameBorder="0" scrolling="auto"class="dp-attachment-pdf"></embed>
</div>
