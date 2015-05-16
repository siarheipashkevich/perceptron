(function () {
    'use strict';

    angular.module('app.recognition')
        .controller('ModalValuesYController', ModalValuesYController);

    ModalValuesYController.$inject = ['$modalInstance', '$scope', 'valuesY'];

    function ModalValuesYController($modalInstance, $scope, valuesY) {
        /*jshint validthis: true */
        var vm = this;

        vm.matrixValuesY = valuesY;

        vm.cancel = function () {
            $modalInstance.dismiss('cancel');
        };
    }
})();