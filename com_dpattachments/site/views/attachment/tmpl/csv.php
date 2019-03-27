<?php
/**
 * @package    DPAttachments
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2012 - 2019 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$content        = JFile::read(\DPAttachments\Helper\Core::getPath($this->item->path, $this->item->context));
$delimiter      = ',';
$delimiterCount = 0;
foreach (array(',', ';', "\t") as $char) {
	$tmp = substr_count($content, $char);
	if ($tmp > $delimiterCount) {
		$delimiter      = $char;
		$delimiterCount = $tmp;
	}
}
$csv = new ParseCsv\Csv();
$csv->encoding('UTF-16', 'UTF-8');
$csv->delimiter = $delimiter;
$csv->parse($content);

JHtml::_('stylesheet', 'com_dpattachments/views/attachment/csv.min.css', ['relative' => true]);
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
					<td><?php echo nl2br(htmlentities($value)); ?></td>
				<?php } ?>
			</tr>
		<?php } ?>
	</table>
</div>