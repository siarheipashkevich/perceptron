(function () {
    'use strict';

    angular.module('app.recognition')
        .config(Config);

    Config.$inject = ['$stateProvider', '$urlRouterProvider'];

    function Config($stateProvider, $urlRouterProvider) {
        $stateProvider
            .state('recognition', {
                url: '/recognition',
                templateUrl: '/js/app/recognition/recognition.html',
                controller: 'RecognitionController as vm'
            });
    }
})();