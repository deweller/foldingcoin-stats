let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

// mix.copyDirectory('node_modules/@fortawesome/fontawesome-free/webfonts', 'public/fonts/font-awesome');

mix.js('resources/assets/js/app.js', 'public/js')
   .extract([
        'axios',
        'vue',
        'highcharts',
        'moment',
        'moment-timezone',
        'numeral',
    ])
   .sass('resources/assets/sass/app.scss', 'public/css')


