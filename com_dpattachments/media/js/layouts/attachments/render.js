if (!Element.prototype.matches) {
	Element.prototype.matches =
		Element.prototype.matchesSelector ||
		Element.prototype.mozMatchesSelector ||
		Element.prototype.msMatchesSelector ||
		Element.prototype.oMatchesSelector ||
		Element.prototype.webkitMatchesSelector ||
		function (s) {
			var matches = (this.document || this.ownerDocument).querySelectorAll(s),
				i = matches.length;
			while (--i >= 0 && matches.item(i) !== this) {
			}
			return i > -1;
		};
}

(function (document, Joomla) {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelector('.com-dpattachments-layout-attachments').addEventListener('click', function (e) {
			if (!e.target.matches('.dp-attachment__link')) {
				return true;
			}

			e.preventDefault();

			var modalFunction = function modalFunction(src) {
				var modal = new tingle.modal({
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
				var resource = document.createElement('script');
				resource.type = 'text/javascript';
				resource.src = Joomla.getOptions('system.paths').root + '/media/com_dpattachments/js/tingle/tingle.min.js';
				resource.addEventListener('load', function () {
					modalFunction(e.target.getAttribute('href'));
				});
				document.head.appendChild(resource);

				var l = document.createElement('link');
				l.rel = 'stylesheet';
				l.href = Joomla.getOptions('system.paths').root + '/media/com_dpattachments/css/tingle/tingle.min.css';
				document.head.appendChild(l);

				return false;
			}

			modalFunction(e.target.getAttribute('href'));

			return false;
		});
	});
})(document, Joomla);
