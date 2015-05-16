(function () {
    'use strict';

    angular.module('app.recognition')
        .controller('ModalCompoundsController', ModalCompoundsController);

    ModalCompoundsController.$inject = ['$modalInstance', '$scope', 'compounds', 'selectedImage'];

    function ModalCompoundsController($modalInstance, $scope, compounds, selectedImage) {
        /*jshint validthis: true */
        var vm = this;

        vm.tableConnection = compounds;
        vm.selectedImage = selectedImage;

        vm.cancel = function () {
            $modalInstance.dismiss('cancel');
        };
    }
})();