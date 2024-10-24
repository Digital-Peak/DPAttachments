<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

\defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

$content = file_get_contents(Factory::getApplication()->bootComponent('dpattachments')->getPath($this->item->path, $this->item->context));

HTMLHelper::_('stylesheet', 'com_dpattachments/dpattachments/views/attachment/txt.min.css', ['relative' => true]);
?>
<div class="com-dpattachments-attachment com-dpattachments-attachment-txt">
	<h3 class="com-dpattachments-attachment__header"><?php echo $this->escape($this->item->title); ?></h3>
	<div class="com-dpattachments-attachment__content"><?php echo htmlentities($content ?: ''); ?></div>
</div>
