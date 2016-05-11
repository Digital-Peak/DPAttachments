<?php
/**
 * @package    DPAttachments
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2012 - 2015 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$attachment = $displayData['attachment'];
if (!$attachment)
{
	return;
}

$doc = JFactory::getDocument();

$buffer = '<form action="' . JRoute::_('index.php?option=com_dpattachments&task=attachment.upload') .
		 '" method="get" class="alert alert-info dpattachments-fileupload-form" id="dpattachments-fileupload-' . $attachment->id . '">';
$buffer .= '<span class="clearfix">' . JText::_('COM_DPATTACHMENTS_TEXT_SELECT_FILE') . ' ';
$buffer .= '<span id="dpattachments-text-drag-' . $attachment->id . '">' . JText::_('COM_DPATTACHMENTS_TEXT_DROP') . '</span> ';
$buffer .= JText::_('COM_DPATTACHMENTS_TEXT_TO_UPLOAD') . '</span> ';
$buffer .= '<input type="file" name="file">';
$buffer .= '<p id="dpattachments-text-paste-' . $attachment->id . '">' . JText::_('COM_DPATTACHMENTS_TEXT_PASTE') . '</p> ';
$buffer .= '<input type="hidden" name="attachment[context]" value="' . $attachment->context . '" />';
$buffer .= '<input type="hidden" name="attachment[item_id]" value="' . $attachment->id . '" />';
$buffer .= JHtml::_('form.token');
$buffer .= '<div class="progress progress-striped progress-success active hide" id="dpattachments-progress-' . $attachment->id .
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

					var fd = new FormData(jQuery('#dpattachments-fileupload-" . $attachment->id . "')[0]);
				    fd.append('file', file);

        	        jQuery('#dpattachments-progress-" . $attachment->id . "').show();
        	        jQuery('#dpattachments-progress-" . $attachment->id . " div').html('<p>0%</p>');
					jQuery.ajax({
					    url: jQuery('#dpattachments-fileupload-" . $attachment->id . "').attr('action'),
					    data: fd,
					    processData: false,
					    contentType: false,
	                    type: 'POST',
                        xhr: function(){
                            var myXHR = jQuery.ajaxSettings.xhr();
                            myXHR.upload.addEventListener('progress', function (e) {
			        	        if (e.lengthComputable) {
			            	        var percentage = Math.round((e.loaded * 100) / e.total);
			            	        jQuery('#dpattachments-progress-" . $attachment->id . " div').html('<p>'+percentage+'%</p>');
			            	        jQuery('#dpattachments-progress-" . $attachment->id . " div').css('width', percentage + '%');
			        	        }
					        }, false);
                            return myXHR;
                        },
	                    success: function(responseText){
	                       var json = jQuery.parseJSON(responseText);
					       Joomla.renderMessages(json.messages);

                	       jQuery('#dpattachments-progress-" . $attachment->id . "').fadeOut();
                           jQuery('#dpattachments-container-" . $attachment->id . "').append(json.data.html);
	                    }
					});
				}
			}
		}
	};

	jQuery('#dpattachments-fileupload-" . $attachment->id . ", #dpattachments-fileupload-" . $attachment->id . " input[type=file]').fileReaderJS(opts);
	if (jQuery('.dpattachments-fileupload-form').size() == 1) {
		jQuery('body').fileClipboard(opts);
	}
    if (!FileReaderJS.enabled) {
        jQuery('#dpattachments-text-paste-" . $attachment->id . "').hide();
        jQuery('#dpattachments-text-drag-" . $attachment->id . "').hide();
    }
    if (typeof jQuery('body')[0]['onpaste'] != 'object') {
        jQuery('#dpattachments-text-paste-" . $attachment->id . "').hide();
    }
    jQuery(':file').filestyle({buttonText: '" . JText::_('COM_DPATTACHMENTS_BUTTON_SELECT_FILE') . "', classButton: 'btn btn-small', input: false});
});");

echo $buffer;