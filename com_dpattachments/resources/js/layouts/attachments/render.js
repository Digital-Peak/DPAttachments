/**
 * @package   DPAttachments
 * @copyright Copyright (C) 2018 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

let dpattachmentsModal = null;

document.addEventListener('DOMContentLoaded', () => {
	window.document.addEventListener('dpattachmentSaved', () => {
		if (!dpattachmentsModal) {
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
		const modalFunction = (src) => {
			dpattachmentsModal = new tingle.modal({
				footer: false,
				stickyFooter: false,
				closeMethods: ['overlay', 'button', 'escape'],
				cssClass: ['dp-attachment-modal'],
				closeLabel: Joomla.JText._('TMPL_DPSTRAP_CLOSE', 'Close'),
				onClose: function () {
					dpattachmentsModal.destroy();
				}
			});

			dpattachmentsModal.setContent('<iframe class="dp-attachment-modal__content" src="' + src + '"></iframe>');
			dpattachmentsModal.open();
		};

		if (typeof tingle === 'undefined' || !tingle) {
			const resource = document.createElement('script');
			resource.type = 'text/javascript';
			resource.src = Joomla.getOptions('system.paths').root + '/media/com_dpattachments/js/vendor/tingle/tingle.min.js';
			resource.addEventListener('load', () => modalFunction(link));
			document.head.appendChild(resource);

			const l = document.createElement('link');
			l.rel = 'stylesheet';
			l.href = Joomla.getOptions('system.paths').root + '/media/com_dpattachments/css/vendor/tingle/tingle.min.css';
			document.head.appendChild(l);

			return false;
		}

		modalFunction(link);
	}

	function delegateSelector(selector, event, childSelector, handler) {
		Array.from(document.querySelectorAll(selector)).forEach((el) => {
			el.addEventListener(event, (e) => e.target.matches(childSelector) ? handler(e) : null);
		});
	}
});
