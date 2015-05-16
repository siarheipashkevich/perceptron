(function () {
    'use strict';

    angular.module('app.recognition')
        .controller('ModalWeightsSumController', ModalWeightsSumController);

    ModalWeightsSumController.$inject = ['$modalInstance', '$scope', 'originWeights', 'weightsSum'];

    function ModalWeightsSumController($modalInstance, $scope, originWeights, weightsSum) {
        /*jshint validthis: true */
        var vm = this;

        vm.originWeights = originWeights;
        vm.weightsSum = weightsSum;

        vm.cancel = function () {
            $modalInstance.dismiss('cancel');
        };
    }
})();