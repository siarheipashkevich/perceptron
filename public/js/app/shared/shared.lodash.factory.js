(function () {
    'use strict';

    angular.module('app.shared')
        .factory('_', LodashFactory);

    LodashFactory.$inject = ['$window'];

    function LodashFactory($window) {
        return $window._;
    }
})();
