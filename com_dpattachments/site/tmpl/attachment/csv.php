<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

\defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use ParseCsv\Csv;

$content        = file_get_contents(Factory::getApplication()->bootComponent('dpattachments')->getPath($this->item->path, $this->item->context));
$delimiter      = ',';
$delimiterCount = 0;
foreach ([',', ';', "\t"] as $char) {
	$tmp = substr_count($content ?: '', $char);
	if ($tmp > $delimiterCount) {
		$delimiter      = $char;
		$delimiterCount = $tmp;
	}
}
$csv = new Csv();
$csv->encoding('UTF-16', 'UTF-8');
$csv->delimiter = $delimiter;
$csv->parse($content ?: '');

HTMLHelper::_('stylesheet', 'com_dpattachments/dpattachments/views/attachment/csv.min.css', ['relative' => true]);
?>
<div class="com-dpattachments-attachment com-dpattachments-attachment-csv">
	<h3 class="com-dpattachments-attachment__header"><?php echo $this->escape($this->item->title); ?></h3>
	<table class="com-dpattachments-attachment__content dp-table">
		<tr>
			<?php foreach ($csv->titles as $title) { ?>
				<td><?php echo $title; ?></td>
			<?php } ?>
		</tr>
		<?php foreach ($csv->data as $row) { ?>
			<tr>
				<?php foreach ($row as $value) { ?>
					<td><?php echo nl2br(htmlentities((string) $value)); ?></td>
				<?php } ?>
			</tr>
		<?php } ?>
	</table>
</div>
