/**
 * @package   DPAttachments
 * @copyright Copyright (C) 2018 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

let dpattachmentsModal = null;

window.document.addEventListener('dpattachmentSaved', (e) => {
	if (!dpattachmentsModal) {
		return;
	}

	// Just close when the attachment form is cancelled
	if (e.detail === 'attachment.cancel') {
		dpattachmentsModal.close();
		return;
	}

	const iframe = document.querySelector('.dp-attachment-modal__content');
	if (!iframe) {
		return;
	}

	iframe.addEventListener('load', () => setTimeout(() => location.reload(), 2000));
});

delegateSelector('.com-dpattachments-layout-attachments', 'click', '.dp-button-edit', (e) => {
	e.preventDefault();

	openModal(e.target.href);

	return false;
});

delegateSelector('.com-dpattachments-layout-attachments', 'click', '.dp-button-trash', (e) => {
	e.preventDefault();

	fetch(e.target.href).then((response) => {
		if (!response.ok) {
			return;
		}

		e.target.closest('.dp-attachment').remove();
	});

	return false;
});

delegateSelector('.com-dpattachments-layout-attachments', 'click', '.dp-attachment__link', (e) => {
	e.preventDefault();

	openModal(e.target.getAttribute('href'));

	return false;
});

function openModal(link) {
	if (!dpattachmentsModal) {
		import('tingle.js').then(() => {
			dpattachmentsModal = new tingle.modal({
				footer: false,
				stickyFooter: false,
				closeMethods: ['overlay', 'button', 'escape'],
				cssClass: ['dp-attachment-modal'],
				closeLabel: Joomla.JText._('TMPL_DPSTRAP_CLOSE', 'Close'),
				onClose: function () {
					dpattachmentsModal.destroy();
					dpattachmentsModal = null;
				}
			});

			dpattachmentsModal.setContent('<iframe class="dp-attachment-modal__content" src="' + link + '"></iframe>');
			dpattachmentsModal.open();
		});

		import('tingle.js/src/tingle.css');

		return;
	}

	dpattachmentsModal.setContent('<iframe class="dp-attachment-modal__content" src="' + link + '"></iframe>');
	//dpattachmentsModal.open();
}

function delegateSelector(selector, event, childSelector, handler) {
	document.querySelectorAll(selector).forEach((el) => {
		el.addEventListener(event, (e) => e.target.matches(childSelector) ? handler(e) : null);
	});
}
