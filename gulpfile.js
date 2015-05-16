var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Less
 | file for our application, as well as publishing vendor resources.
 |
 */
var paths = {
    'angular': 'bower_components/angular/',
    'angularUiRouter': 'bower_components/angular-ui-router/release/',
    'angularUiBootstrap': 'bower_components/angular-bootstrap/',
    'angularUiBootstrapTpls': 'bower_components/angular-bootstrap/',
    'ngFileUpload': 'bower_components/ng-file-upload/',
    'lodash': 'bower_components/lodash/',
    'fontawesome': ['bower_components/fontawesome/less']
};

elixir(function(mix) {
    mix.less('app.less', './public/css/', { paths: paths.fontawesome });

    //mix.less(paths.fontawesome + 'font-awesome.less', './public/css/', { paths: paths.fontawesome });

    mix.scripts([
        paths.angular + 'angular.js',
        paths.angularUiRouter + 'angular-ui-router.js',
        paths.angularUiBootstrap + 'ui-bootstrap.js',
        paths.angularUiBootstrapTpls + 'ui-bootstrap-tpls.js',
        paths.ngFileUpload + 'angular-file-upload.js',
        paths.lodash + 'lodash.js'
    ], 'public/js/dist/lib.js', './public/');

    mix.copy('./public/bower_components/fontawesome/fonts', 'public/fonts');
});