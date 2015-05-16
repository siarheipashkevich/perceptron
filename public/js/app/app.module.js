(function () {
    'use strict';

    angular.module('app', [
        'app.core',
        'app.shared',
        'app.recognition'
    ]);

    angular.element(document).ready(function() {
        angular.bootstrap(document, ['app']);
    });
})();
