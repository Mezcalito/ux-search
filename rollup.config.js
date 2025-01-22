const resolve = require('@rollup/plugin-node-resolve');
const alias = require('@rollup/plugin-alias');
const commonjs = require('@rollup/plugin-commonjs');
const typescript = require('@rollup/plugin-typescript');
const del = require('rollup-plugin-delete');
const sass = require('rollup-plugin-sass');
const path = require('path');
const glob = require('glob')

module.exports = {
  input: [
    'assets/src/controller.ts',
    ...glob.sync('assets/src/controllers/**/*.ts')
  ],
  output: {
    dir: 'assets/dist',
    entryFileNames: '[name].js',
    format: 'es',
    preserveModules: true,
    preserveModulesRoot: 'assets/src',
  },
  external: ['@hotwired/stimulus', '@symfony/ux-live-component'],
  plugins: [
    del({targets: 'assets/dist/*'}),
    resolve(),
    typescript({
      tsconfig: './tsconfig.json',
      declaration: true,
      declarationDir: './assets/dist',
    }),
    commonjs(),
    sass({
      output: 'assets/dist/default.min.css',
      options: {
        outputStyle: 'compressed',
      },
    }),
    alias({
      entries: [
        {
          find: '@symfony/ux-live-component',
          replacement: path.resolve(__dirname, 'vendor/symfony/ux-live-component/assets/dist'),
        },
      ],
    }),
  ],
};
