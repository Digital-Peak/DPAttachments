<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2021 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

\defined('_JEXEC') or die();

use Joomla\CMS\Layout\LayoutHelper;

?>
<div class="com-dpattachments-attachments__filters">
	<?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
</div>
