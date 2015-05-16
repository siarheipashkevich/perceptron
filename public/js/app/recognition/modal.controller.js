(function () {
    'use strict';

    angular.module('app.recognition')
        .controller('ModalController', ModalController);

    ModalController.$inject = ['$modalInstance', '$scope', 'data'];

    function ModalController($modalInstance, $scope, data) {
        /*jshint validthis: true */
        var vm = this;

        vm.data = data.data.map(function (val) {
            val.weights = angular.fromJson(val.weights);
            val.weightsSum = angular.fromJson(val.weightsSum);
            val.valuesY = angular.fromJson(val.valuesY);
            val.compounds = angular.fromJson(val.compounds);
            val.images = angular.fromJson(val.images);

            val.selected = false;

            return val;
        });

        vm.select = function () {
            $modalInstance.close(vm.item);
        };

        vm.cancel = function () {
            $modalInstance.dismiss('cancel');
        };

        vm.changeSelection = function (data) {
            if (vm.item) {
                vm.item.selected = false;
            }

            vm.item = data;
            vm.item.selected = true;
        }
    }
})();