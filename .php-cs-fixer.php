<?php

$config = new PhpCsFixer\Config();

$config->setRules([
		'@PSR12'                          => true,
		'array_syntax'                    => ['syntax' => 'short'],
		'whitespace_after_comma_in_array' => true,
		'indentation_type'                => true,
		'no_break_comment'                => false,
		'binary_operator_spaces'          => [
			'default'   => 'single_space',
			'operators' => [
				'||' => 'single_space',
				'&&' => 'single_space',
				'='  => 'align_single_space_minimal',
				'+=' => 'align_single_space_minimal',
				'=>' => 'align_single_space_minimal'
			]
		],
		'no_useless_else' => true
	])
	->setUsingCache(false)
	->setIndent("\t");

return $config;
