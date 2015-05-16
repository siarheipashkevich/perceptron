(function () {
    'use strict';

    angular.module('app.recognition')
        .controller('ModalMainController', ModalMainController);

    ModalMainController.$inject = ['$modalInstance', '$http', '$scope', '$upload', 'RecognitionService', 'ImageService', '_'];

    function ModalMainController($modalInstance, $http, $scope, $upload, RecognitionService, ImageService, _) {
        /*jshint validthis: true */
        var vm = this;

        vm.selectFile = selectFile;
        vm.convertColorToGrayscale = convertColorToGrayscale;
        vm.convertGrayscaleToBinary = convertGrayscaleToBinary;
        vm.horizontalChart = horizontalChart;
        vm.cutStrings = cutStrings;
        vm.cutCharacters = cutCharacters;
        vm.recognitionCharacters = recognitionCharacters;

        vm.cancel = function () {
            $modalInstance.dismiss('cancel');
        };

        function selectFile(images) {
            var data = {
                'type': 'color'
            };

            if (images && images.length) {
                for (var i = 0; i < images.length; i++) {
                    var image = images[i];

                    $upload.upload({
                        url: 'api/upload',
                        fields: data,
                        file: image
                    }).success(function (response) {
                        vm.originFile = response;
                        console.log(response);
                    });
                }
            }
        }

        function convertColorToGrayscale() {
            $http.post('api/convertGrayscale', {image: vm.originFile})
                .success(function (response) {
                    vm.grayscaleFile = response;
                });
        }

        function convertGrayscaleToBinary() {
            $http.post('api/convertBinary', {image: vm.grayscaleFile, threshold: vm.threshold})
                .success(function (response) {
                    vm.binaryFile = response;
                });
        }

        function horizontalChart() {
            $http.post('api/horizontalChart', { matrix: vm.binaryFile.matrix})
                .success(function (response) {
                    var horizontalChartElement = angular.element(document.querySelector('#horizontalChart'));

                    vm.foundAreas = response.foundAreas;

                    horizontalChartElement.html(response.template);
                });
        }

        function cutStrings() {
            $http.post('api/cutLines', { foundAreas: vm.foundAreas, imagePath: vm.binaryFile.path })
                .success(function (response) {
                    var verChartOne = angular.element(document.querySelector('.verticalChart.one'));
                    var verChartTwo = angular.element(document.querySelector('.verticalChart.two'));

                    vm.cutLines = response;

                    verChartOne.html(vm.cutLines[0].template);
                    verChartTwo.html(vm.cutLines[1].template);
                });
        }

        function cutCharacters() {
            $http.post('api/cutCharacters', { cutLines: vm.cutLines })
                .success(function (response) {
                    vm.cutCharacters = response;
                });
        }

        function recognitionCharacters() {
            var recognitionCharacters = [], matrix, max, index, rec,
                weights = RecognitionService.getWeights();

            vm.cutCharacters.forEach(function (character) {
                matrix = ImageService.convertTwoDimensionalMatrixToSingleMatrix(character.matrix);
                rec = RecognitionService.recognition(matrix, 1);

                max = _.max(rec);
                index = _.findIndex(rec, function (value) {
                    return value == max;
                });

                recognitionCharacters.push(weights[index].class);
            });

            console.log(recognitionCharacters);
        }
    }
})();

// WenQuanYi Micro Hei 18px