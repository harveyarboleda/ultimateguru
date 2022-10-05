const mix = require('laravel-mix');

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

mix.js('resources/js/app.js', 'public/js')
.js('resources/js/tags.js', 'public/js')
.css('resources/css/navbar.css', 'public/css')
.css('resources/css/footer.css', 'public/css')
.css('resources/css/style.css', 'public/css')
.sass('resources/sass/app.scss', 'public/css')
.css('resources/css/sbadmin5.css', 'public/css')
.js('resources/js/scripts.js', 'public/js')
.js('resources/js/chartjs.js', 'public/js')
.sourceMaps();
