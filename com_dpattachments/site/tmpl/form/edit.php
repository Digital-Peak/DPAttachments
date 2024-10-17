<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

\defined('_JEXEC') or die();

use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('stylesheet', 'com_dpattachments/dpattachments/views/form/edit.min.css', ['relative' => true]);

HTMLHelper::_('behavior.core');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('script', 'com_dpattachments/views/form/edit.min.js', ['relative' => true, 'version' => 'auto'], ['defer' => true, 'type' => 'module']);
?>
<div class="com-dpattachments-attachment-form<?php echo $this->pageclass_sfx ? ' com-dpattachments-attachment-form-' . $this->pageclass_sfx : ''; ?>">
	<?php echo $this->loadtemplate('heading'); ?>
	<?php echo $this->loadtemplate('actions'); ?>
	<?php echo $this->loadtemplate('form'); ?>
</div>
