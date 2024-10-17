<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2018 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

\defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

?>
<div class="com-dpattachments-attachment-form__actions">
	<div class="dp-button-group">
		<button type="button" class="dp-button dp-button-save" data-task="save">
			<?php echo LayoutHelper::render('block.icon', ['icon' => 'check']); ?>
			<?php echo Text::_('JSAVE'); ?>
		</button>
		<button type="button" class="dp-button dp-button-cancel" data-task="cancel">
			<?php echo LayoutHelper::render('block.icon', ['icon' => 'ban']); ?>
			<?php echo Text::_('JCANCEL'); ?>
		</button>
	</div>
</div>
