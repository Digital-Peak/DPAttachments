/**
 *
 * @package   DPAttachments
 * @author    Digital Peak http://www.digital-peak.com
 * @copyright Copyright (C) 2012 - 2020 Digital Peak. All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

((document, Joomla) => {
	'use strict';

	document.addEventListener('DOMContentLoaded', () => {
		Joomla.submitbutton = function (task) {
			if (task == 'attachment.cancel' || document.formvalidator.isValid(document.getElementById('adminForm'))) {
				Joomla.submitform(task, document.querySelector('.com-dpattachments-form-edit .dp-form'));
			}
		};

		[].slice.call(document.querySelectorAll('.com-dpattachments-form-edit__actions .dp-button')).forEach((button) => {
			button.addEventListener('click', (e) => {
				Joomla.submitbutton('attachment.' + e.target.getAttribute('data-task'));
			});
		});
	});
})(document, Joomla);
