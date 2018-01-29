<?php
/**
 * @package    DPAttachments
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2012 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$itemId = $displayData['itemId'];
if (!$itemId)
{
	return;
}
$context = $displayData['context'];
if (!$context)
{
	return;
}

JFactory::getLanguage()->load('com_dpattachments', JPATH_ADMINISTRATOR . '/components/com_dpattachments');

$doc = JFactory::getDocument();

$buffer = '<form action="' . JRoute::_('index.php?option=com_dpattachments&task=attachment.upload') .
		 '" method="get" class="alert alert-info dpattachments-fileupload-form" id="dpattachments-fileupload-' . $itemId . '">';
$buffer .= '<span class="clearfix">' . JText::_('COM_DPATTACHMENTS_TEXT_SELECT_FILE') . ' ';
$buffer .= '<span id="dpattachments-text-drag-' . $itemId . '">' . JText::_('COM_DPATTACHMENTS_TEXT_DROP') . '</span> ';
$buffer .= JText::_('COM_DPATTACHMENTS_TEXT_TO_UPLOAD') . '</span> ';
$buffer .= '<input type="file" name="file">';
$buffer .= '<p id="dpattachments-text-paste-' . $itemId . '">' . JText::_('COM_DPATTACHMENTS_TEXT_PASTE') . '</p> ';
$buffer .= '<input type="hidden" name="attachment[context]" value="' . $context . '" />';
$buffer .= '<input type="hidden" name="attachment[item_id]" value="' . $itemId . '" />';
$buffer .= JHtml::_('form.token');
$buffer .= '<div class="progress progress-striped progress-success active hide" id="dpattachments-progress-' . $itemId .
		 '"><div class="bar" style="text-align:left"></div></div>';
$buffer .= '</form>';

JHtml::_('script', 'system/core.js', false, true);
JHtml::_('jquery.framework');
$doc->addScript(JUri::root() . '/components/com_dpattachments/libraries/uploader/filereader.min.js');

$doc->addScriptDeclaration(
		"jQuery(document).ready(function(){
	var opts = {
		dragClass: 'alert-success',
		readAsMap: {},
		on: {
			groupstart: function(group) {
				for (var i = 0; i < group.files.length; i++) {
					var file = group.files[i];

					var fd = new FormData(jQuery('#dpattachments-fileupload-" . $itemId . "')[0]);
				    fd.append('file', file);

        	        jQuery('#dpattachments-progress-" . $itemId . "').show();
        	        jQuery('#dpattachments-progress-" . $itemId . " div').html('<p>0%</p>');
					jQuery.ajax({
					    url: jQuery('#dpattachments-fileupload-" . $itemId . "').attr('action'),
					    data: fd,
					    processData: false,
					    contentType: false,
	                    type: 'POST',
                        xhr: function(){
                            var myXHR = jQuery.ajaxSettings.xhr();
                            myXHR.upload.addEventListener('progress', function (e) {
			        	        if (e.lengthComputable) {
			            	        var percentage = Math.round((e.loaded * 100) / e.total);
			            	        jQuery('#dpattachments-progress-" . $itemId . " div').html('<p>'+percentage+'%</p>');
			            	        jQuery('#dpattachments-progress-" . $itemId . " div').css('width', percentage + '%');
			        	        }
					        }, false);
                            return myXHR;
                        },
	                    success: function(responseText){
	                       var json = jQuery.parseJSON(responseText);
					       Joomla.renderMessages(json.messages);

                	       jQuery('#dpattachments-progress-" . $itemId . "').fadeOut();
                           jQuery('#dpattachments-container-" . $context . '-' . $itemId . "').append(json.data.html);
	                    }
					});
				}
			}
		}
	};

	jQuery('#dpattachments-fileupload-" . $itemId .
				 ", #dpattachments-fileupload-" . $itemId . " input[type=file]').fileReaderJS(opts);
	if (jQuery('.dpattachments-fileupload-form').size() == 1) {
		jQuery('body').fileClipboard(opts);
	}
    if (!FileReaderJS.enabled) {
        jQuery('#dpattachments-text-paste-" . $itemId . "').hide();
        jQuery('#dpattachments-text-drag-" . $itemId . "').hide();
    }
    if (typeof jQuery('body')[0]['onpaste'] != 'object') {
        jQuery('#dpattachments-text-paste-" . $itemId . "').hide();
    }
    jQuery(':file').filestyle({buttonText: '" .
				 JText::_('COM_DPATTACHMENTS_BUTTON_SELECT_FILE') . "', classButton: 'btn btn-small', input: false});
});");

echo $buffer;
