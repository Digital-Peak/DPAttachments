((document, Joomla) => {
	'use strict';

	const processFileList = function (input, files) {
		if (!files.length) {
			return;
		}

		Array.prototype.forEach.call(files, (file) => {
			const progress = input.form.querySelector('.dp-form__progress');
			progress.style.display = 'block';
			progress.value = 0;

			// We do our own because no content type must be set
			const xhr = new XMLHttpRequest();
			xhr.open('post', input.form.getAttribute('action'), true);
			xhr.setRequestHeader('X-CSRF-Token', Joomla.getOptions('csrf.token', ''));
			xhr.onreadystatechange = function () {
				// Request not finished
				if (xhr.readyState !== 4) {
					return;
				}
				progress.style.display = 'none';

				// Request finished and response is ready
				if (xhr.status === 200) {
					const json = JSON.parse(xhr.responseText);
					Joomla.renderMessages(json.messages);

					var container = document.querySelector('.com-dpattachments-layout-attachments__attachments[data-context="' + json.data.context + '"][data-item="' + json.data.item_id + '"]');
					container.parentElement.classList.remove('com-dpattachments-layout-attachments_empty');
					container.innerHTML += json.data.html;

					input.value = '';
				}
			};

			xhr.upload.addEventListener('progress', (e) => {
				if (e.lengthComputable) {
					progress.value = Math.round((e.loaded * 100) / e.total);
				}
			}, false);

			const fd = new FormData(input.form);
			fd.append('file', file);
			xhr.send(fd);
		});
	};

	document.addEventListener('DOMContentLoaded', () => {
		[].slice.call(document.querySelectorAll('.com-dpattachments-layout-form .dp-input__file')).forEach((input, index) => {
			input.addEventListener('change', (e) => {
				processFileList(input, input.files);
			}, false);
			input.addEventListener('drop', (e) => {
				e.stopPropagation();
				e.preventDefault();
				processFileList(input, e.dataTransfer.files);
			}, false);

			if (index == 0) {
				document.querySelector('body').addEventListener('paste', (e) => {
					const files = [];
					const clipboardData = e.clipboardData || {};
					const items = clipboardData.items || [];

					for (let i = 0; i < items.length; i++) {
						const file = items[i].getAsFile();

						if (!file) {
							continue;
						}

						// Create a fake file name for images from clipboard, since this data doesn't get sent
						const matches = new RegExp("/\(.*\)").exec(file.type);
						if (!file.name && matches) {
							const extension = matches[1];
							file.name = "clipboard" + i + "." + extension;
						}

						files.push(file);
					}

					if (files.length) {
						processFileList(input, files);
						e.preventDefault();
						e.stopPropagation();
					}
				}, false);
			}

			[].slice.call(document.querySelectorAll('.com-dpattachments-layout-form')).forEach(function (form) {
				// Copied from https://github.com/bgrins/filereader.js
				var initializedOnBody = false;

				// Bind drag events to the form to add the class while dragging, and accept the drop data transfer
				form.addEventListener('dragenter', function (e) {
					if (initializedOnBody) {
						return;
					}
					e.stopPropagation();
					e.preventDefault();
					form.classList.add('com-dpattachments-layout-form_drag');
				}, false);
				form.addEventListener('dragleave', function (e) {
					if (initializedOnBody) {
						return;
					}
					e.stopPropagation();
					e.preventDefault();
					form.classList.remove('com-dpattachments-layout-form_drag');
				}, false);
				form.addEventListener('dragover', function (e) {
					if (initializedOnBody) {
						return;
					}
					e.stopPropagation();
					e.preventDefault();
					form.classList.add('com-dpattachments-layout-form_drag');
				}, false);
				form.addEventListener('drop', function (e) {
					if (initializedOnBody) {
						return;
					}
					e.stopPropagation();
					e.preventDefault();
					form.classList.remove('com-dpattachments-layout-form_drag');
					processFileList(form.querySelector('.dp-input__file'), e.dataTransfer.files);
				}, false);

				// Bind to body to prevent the form events from firing when it was initialized on the page
				document.body.addEventListener('dragstart', function (e) {
					initializedOnBody = true;
				}, true);
				document.body.addEventListener('dragend', function (e) {
					initializedOnBody = false;
				}, true);
				document.body.addEventListener('drop', function (e) {
					if (e.dataTransfer.files && e.dataTransfer.files.length) {
						e.stopPropagation();
						e.preventDefault();
					}
				}, false);
			});
		});
	});
})(document, Joomla);
