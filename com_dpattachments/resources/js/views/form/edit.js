/**
 * @package   DPAttachments
 * @copyright Copyright (C) 2018 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

document.addEventListener('DOMContentLoaded', () => {
	Joomla.submitbutton = function (task) {
		const form = document.querySelector('.com-dpattachments-attachment-form .dp-form');
		if (task == 'attachment.cancel' || document.formvalidator.isValid(form)) {
			Joomla.submitform(task, form);
		}
	};

	[].slice.call(document.querySelectorAll('.com-dpattachments-attachment-form__actions .dp-button')).forEach((button) => {
		button.addEventListener('click', (e) => Joomla.submitbutton('attachment.' + e.target.getAttribute('data-task')));
	});
});
