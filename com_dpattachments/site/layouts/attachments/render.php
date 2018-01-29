<?php
use Joomla\Registry\Registry;

/**
 *
 * @package DPAttachments
 * @author Digital Peak http://www.digital-peak.com
 * @copyright Copyright (C) 2012 - 2018 Digital Peak. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$attachments = $displayData['attachments'];
if (!$attachments)
{
	return;
}
$options = $displayData['options'];
if (!$options)
{
	$options = new Registry();
}

JFactory::getLanguage()->load('com_dpattachments', JPATH_ADMINISTRATOR . '/components/com_dpattachments');

$count = count($attachments);
$itemId = $attachments['0']->item_id;
$buffer = '<h4>' . JText::_('COM_DPATTACHMENTS_ATTACHMENTS') . '</h4>';

$buffer .= '<div id="dpattachments-container-' . $attachments[0]->context . '-' . $itemId . '" class="dpattachments-container">';
$columns = $options->get('render.columns', 2);
for ($i = 0; $i < $count; $i ++)
{
	$attachment = $attachments[$i];
	if ($i % $columns == 0)
	{
		$buffer .= '<div class="row-fluid">';
	}
	$buffer .= '<div class="span' . (round(12 / $columns)) . '">';
	$buffer .= JLayoutHelper::render('attachment.render', array(
			'attachment' => $attachment
	), null, array(
			'component' => 'com_dpattachments',
			'client' => 0
	));

	$buffer .= '</div>';
	if ($i % $columns == $columns - 1)
	{
		$buffer .= '</div>';
	}
}
if ($count && ($count - 1) % $columns != $columns - 1)
{
	$buffer .= '</div>';
}
$buffer .= '</div>';

$doc = JFactory::getDocument();
if ($count)
{
	JHtmlBootstrap::framework();
	$buffer .= "<div id='dpattachments-modal-" . $itemId . "' class='modal fade hide' tabindex='-1' role='dialog' aria-hidden='true' style='display:none'>
                <div class='modal-header'>
                    <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>
                    <h3></h3>
                </div>
                <iframe id='dpattachments-iframe-" . $itemId . "' style='zoom:0.60;width:99.6%;height:500px;border:none'></iframe>
                <div class='modal-footer'><button class='btn btn-primary' data-dismiss='modal' aria-hidden='true'>" .
			 JText::_('COM_DPATTACHMENTS_CLOSE') . "</button></div>
              </div>";

	$link = "jQuery(this).attr('title')+";
	$link .= "\" <a href='\"+this.href.replace('tmpl=component', '')+\"' ";
	$link .= "title='" . JText::_('COM_DPATTACHMENTS_TEXT_FULL_SCREEN_MODE') . "'>";
	$link .= "<span class='icon-expand small'></span></a>\"";

	$script = "
            jQuery(document).on('click', '.dpattachments-button', function (event) {
                event.preventDefault();
                jQuery('#dpattachments-iframe-" . $itemId . "').attr('src', this.href);
                jQuery('#dpattachments-modal-" . $itemId . " h3').html(" . $link . ");
                var modal = jQuery('#dpattachments-modal-" . $itemId . "').modal();
                if (jQuery(window).width() < modal.width()) {
					modal.css({ width : jQuery(window).width() - 100 + 'px' });
				} else {
					modal.css({ 'margin-left' : '-' + modal.width() / 2 + 'px' });
				}
            });";
	$doc->addScriptDeclaration('jQuery(document).ready(function(){' . $script . '});');
}

echo $buffer;
