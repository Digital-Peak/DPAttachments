var fs = require('fs');
var uglifyJS = require("uglify-js");
var sass = require('node-sass');

var mediaRoot = '../com_dpattachments/media/';

// DPAttachments - Views
compileSass('views/attachment/csv', '../com_dpattachments/media/', 'views/attachment/csv.css');
compress('css/views/attachment/csv', 'css');
compileSass('views/attachment/patch', '../com_dpattachments/media/', 'views/attachment/patch.css');
compress('css/views/attachment/patch', 'css');
compileSass('views/attachment/image', '../com_dpattachments/media/', 'views/attachment/image.css');
compress('css/views/attachment/image', 'css');
compileSass('views/form/edit', '../com_dpattachments/media/', 'views/form/edit.css');
compress('css/views/form/edit', 'css');
compress('js/views/form/edit', 'js');

// DPAttachments - Layouts
compileSass('layouts/attachment/form', '../com_dpattachments/media/', 'layouts/attachment/form.css');
compress('css/layouts/attachment/form', 'css');
compress('js/layouts/attachment/form', 'js');

compileSass('layouts/attachments/render', '../com_dpattachments/media/', 'layouts/attachments/render.css');
compress('css/layouts/attachments/render', 'css');
compress('js/layouts/attachments/render', 'js');

// Copy modal lib
fs.copyFileSync('node_modules/tingle.js/dist/tingle.js', mediaRoot + 'js/tingle/tingle.js');
compress('js/tingle/tingle', 'js', mediaRoot);
fs.copyFileSync('node_modules/tingle.js/dist/tingle.css', mediaRoot + 'css/tingle/tingle.css');
compress('css/tingle/tingle', 'css', mediaRoot);

function compress(relativeFile, type, root) {
	var file = root + relativeFile + '.';
	if (!fs.existsSync(file + type)) {
		file = 'node_modules/' + relativeFile + '.';
	}
	if (!fs.existsSync(file + type)) {
		file = mediaRoot + relativeFile + '.';
	}

	var content = fs.readFileSync(file + type, "utf8");

	var code = '';
	if (type == 'js') {
		code = uglifyJS.minify(content).code
	} else if (type == 'css') {
		code = sass.renderSync({data: content, includePaths: [root], outputStyle: 'compressed'}).css;
	}

	fs.writeFileSync(file + 'min.' + type, code);
}

function compileSass(relativeFile, root, outputPath) {
	if (outputPath == null) {
		outputPath = relativeFile + '.css';
	}

	outputPath = root + '/css/' + outputPath;

	var result = sass.renderSync({
		file: root + '/scss/' + relativeFile + '.scss',
		outFile: outputPath,
		includePaths: [root],
		outputStyle: 'expanded',
		indentType: 'tab',
		indentWidth: 1,
		sourceMap: true
	});

	fs.writeFileSync(outputPath, result.css);
	fs.writeFileSync(outputPath + '.map', result.map);
}
