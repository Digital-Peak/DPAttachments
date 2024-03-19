/**
 * @package   DPAttachments
 * @copyright Copyright (C) 2018 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

document.addEventListener('DOMContentLoaded', () => {
	const processFileList = function (input, files) {
		if (!files.length) {
			return;
		}

		Array.prototype.forEach.call(files, (file) => {
			const container = input.closest('.com-dpattachments-layout-form');
			const progress = container.querySelector('.dp-form__progress');
			progress.style.display = 'block';
			progress.value = 0;

			// We do our own because no content type must be set
			const xhr = new XMLHttpRequest();
			xhr.open('post', container.getAttribute('data-upload-url'), true);
			xhr.setRequestHeader('X-CSRF-Token', Joomla.getOptions('csrf.token', ''));
			xhr.onreadystatechange = () => {
				// Request not finished
				if (xhr.readyState !== 4) {
					return;
				}
				progress.style.display = 'none';

				// Request finished and response is ready
				if (xhr.status === 200) {
					const json = JSON.parse(xhr.responseText);
					Joomla.renderMessages(json.messages);

					const container = document.querySelector(
						'.com-dpattachments-layout-attachments__attachments[data-context="' + json.data.context + '"][data-item="' + json.data.item_id + '"]'
					);
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

			const fd = new FormData();
			fd.append('file', file);
			fd.append('attachment[context]', container.getAttribute('data-context'));
			fd.append('attachment[item_id]', container.getAttribute('data-item'));
			xhr.send(fd);
		});
	};

	[].slice.call(document.querySelectorAll('.com-dpattachments-layout-form .dp-input__file')).forEach((input, index) => {
		input.addEventListener('change', () => processFileList(input, input.files), false);
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
					const matches = new RegExp('/(.*)').exec(file.type);
					if (!file.name && matches) {
						const extension = matches[1];
						file.name = 'clipboard' + i + '.' + extension;
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
	});
	[].slice.call(document.querySelectorAll('.com-dpattachments-layout-form')).forEach((form) => {
		// Copied from https://github.com/bgrins/filereader.js
		let initializedOnBody = false;

		// Bind drag events to the form to add the class while dragging, and accept the drop data transfer
		form.addEventListener('dragenter', (e) => {
			if (initializedOnBody) {
				return;
			}
			e.stopPropagation();
			e.preventDefault();
			form.classList.add('com-dpattachments-layout-form_drag');
		}, false);
		form.addEventListener('dragleave', (e) => {
			if (initializedOnBody) {
				return;
			}
			e.stopPropagation();
			e.preventDefault();
			form.classList.remove('com-dpattachments-layout-form_drag');
		}, false);
		form.addEventListener('dragover', (e) => {
			if (initializedOnBody) {
				return;
			}
			e.stopPropagation();
			e.preventDefault();
			form.classList.add('com-dpattachments-layout-form_drag');
		}, false);
		form.addEventListener('drop', (e) => {
			if (initializedOnBody) {
				return;
			}
			e.stopPropagation();
			e.preventDefault();
			form.classList.remove('com-dpattachments-layout-form_drag');
			processFileList(form.querySelector('.dp-input__file'), e.dataTransfer.files);
		}, false);

		// Bind to body to prevent the form events from firing when it was initialized on the page
		document.body.addEventListener('dragstart', () => initializedOnBody = true, true);
		document.body.addEventListener('dragend', () => initializedOnBody = false, true);
		document.body.addEventListener('drop', (e) => {
			if (e.dataTransfer.files && e.dataTransfer.files.length) {
				e.stopPropagation();
				e.preventDefault();
			}
		}, false);
	});
});
