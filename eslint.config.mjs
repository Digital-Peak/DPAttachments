import js from '../DPDocker/code/config/eslint.config.mjs';

export default [
	...js,
	{
		languageOptions: {
			globals: {
				'tingle': true
			}
		}
	}
];
