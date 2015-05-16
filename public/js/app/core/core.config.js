(function () {
    'use strict';

    angular.module('app.core')
        .config(Config);

    Config.$inject = ['$interpolateProvider', '$urlRouterProvider'];

    function Config($interpolateProvider, $urlRouterProvider) {
        $interpolateProvider.startSymbol('[[');
        $interpolateProvider.endSymbol(']]');

        $urlRouterProvider.otherwise('/');
    }
})();
