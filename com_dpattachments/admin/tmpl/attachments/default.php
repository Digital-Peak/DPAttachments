<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

\defined('_JEXEC') or die();

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('script', 'com_dpattachments/views/attachments/default.min.js', ['relative' => true, 'version' => 'auto'], ['defer' => true, 'type' => 'module']);
?>
<div class="com-dpattachments-attachments">
	<form action="<?php echo Route::_('index.php?option=com_dpattachments&view=attachments'); ?>"
			method="post" name="adminForm" id="adminForm">
		<div id="j-main-container">
			<?php echo $this->loadTemplate('filters'); ?>
			<?php echo $this->loadTemplate('attachments'); ?>
		</div>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="boxchecked" value="0"/>
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
