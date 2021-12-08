/**
 * @package   DPAttachments
 * @copyright Copyright (C) 2018 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

document.addEventListener('DOMContentLoaded', () => {
	[].slice.call(document.querySelectorAll('.com-dpattachments-layout-attachments .dp-attachment__link')).forEach((link) => {
		link.addEventListener('click', e => {
			if (!e.target.matches('.dp-attachment__link')) {
				return true;
			}

			e.preventDefault();

			const modalFunction = (src) => {
				const modal = new tingle.modal({
					footer: false,
					stickyFooter: false,
					closeMethods: ['overlay', 'button', 'escape'],
					cssClass: ['dp-attachment-modal'],
					closeLabel: Joomla.JText._('TMPL_DPSTRAP_CLOSE', 'Close')
				});

				modal.setContent('<iframe class="dp-attachment-preview" src="' + src + '"></iframe>');
				modal.open();
			};

			if (typeof tingle === 'undefined' || !tingle) {
				const resource = document.createElement('script');
				resource.type = 'text/javascript';
				resource.src = Joomla.getOptions('system.paths').root + '/media/com_dpattachments/js/tingle/tingle.min.js';
				resource.addEventListener('load', () => modalFunction(e.target.getAttribute('href')));
				document.head.appendChild(resource);

				const l = document.createElement('link');
				l.rel = 'stylesheet';
				l.href = Joomla.getOptions('system.paths').root + '/media/com_dpattachments/css/tingle/tingle.min.css';
				document.head.appendChild(l);

				return false;
			}

			modalFunction(e.target.getAttribute('href'));

			return false;
		});
	});
});
