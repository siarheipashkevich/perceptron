(function () {
    'use strict';

    angular.module('app.recognition')
        .controller('ModalWeightsController', ModalWeightsController);

    ModalWeightsController.$inject = ['$modalInstance', '$scope', 'data'];

    function ModalWeightsController($modalInstance, $scope, data) {
        /*jshint validthis: true */
        var vm = this;

        vm.data = data;

        vm.cancel = function () {
            $modalInstance.dismiss('cancel');
        };
    }
})();