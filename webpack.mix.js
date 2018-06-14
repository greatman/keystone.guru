const {mix} = require('laravel-mix');

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

let full = false;

// Custom processing only
mix.styles(['resources/assets/css/**/*.css'], 'public/css/custom.css')
    .combine([
        // Doesn't depend on anything
        'resources/assets/js/custom/constants.js',
        // Include in proper order
        'resources/assets/js/custom/dungeonmap.js',
        'resources/assets/js/custom/enemypack.js',
        'resources/assets/js/custom/admin/adminenemypack.js',
        'resources/assets/js/custom/admin/mapcontrol.js',
        // Include the rest
        // 'resources/assets/js/custom/**/*.js'
    ], 'public/js/custom.js');
// .combine(, 'public/js/custom.js');

if (full) {
    mix.js('resources/assets/js/app.js', 'public/js')
        .sass('resources/assets/sass/app.scss', 'public/css')
        // Lib processing
        .styles(['resources/assets/lib/**/*.css'], 'public/css/lib.css')
        .combine('resources/assets/lib/**/*.js', 'public/js/lib.js');
}

mix.sourceMaps();

if (mix.inProduction()) {
    // Copies all tiles as well which takes a while
    mix.copy('resources/assets/images', 'public/images', false);
} else {
    mix.copy('resources/assets/images/lib', 'public/images/lib', false);
    // mix.copy('resources/assets/images/test', 'public/images/test', false);
}