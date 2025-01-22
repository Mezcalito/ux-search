import globals from 'globals';
import pluginJs from '@eslint/js';
import pluginTypescript from 'typescript-eslint';
import pluginPrettierRecommended from 'eslint-plugin-prettier/recommended';

export default [
  { files: ['**/*.{js,mjs,cjs,ts}'] },
  {
    ignores: ['**/build', '**/dist', '**/public', '**/var', '**/vendor', '**/coverage', '**/tests', 'rollup.config.js'],
  },
  { languageOptions: { globals: globals.browser } },
  pluginJs.configs.recommended,
  ...pluginTypescript.configs.recommended,
  pluginPrettierRecommended,

  // Override rules
  {
    rules: {
      'no-duplicate-imports': 'warn',
      'no-console': ['warn', { allow: ['warn', 'error'] }],

      // TypeScript
      '@typescript-eslint/ban-ts-comment': 'off',
      '@typescript-eslint/consistent-type-imports': [
        'warn',
        {
          prefer: 'type-imports',
          disallowTypeAnnotations: false,
          fixStyle: 'inline-type-imports',
        },
      ],
    },
  },
];
