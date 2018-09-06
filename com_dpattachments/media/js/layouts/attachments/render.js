(function (document, Joomla) {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		[].slice.call(document.querySelectorAll('.com-dpattachments-layout-attachments .dp-attachment__link')).forEach(function (link) {
			link.addEventListener('click', function (e) {
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
	});
})(document, Joomla);
