(function () {
    'use strict';

    angular.module('app.recognition')
        .controller('RecognitionController', RecognitionController);

    RecognitionController.$inject = ['$log', '$http', '$modal', '$scope', '$upload', '_', 'RecognitionService', 'ImageService'];

    function RecognitionController($log, $http, $modal, $scope, $upload, _, RecognitionService, ImageService) {
        /*jshint validthis: true */
        var vm = this;

        vm.originWeights = RecognitionService.getWeights();
        vm.weightsSum = RecognitionService.getWeightsSum();
        vm.weights = [];
        vm.tableWeights = [];
        vm.images = ImageService.images;
        vm.selectedImage = ImageService.selectedImage;

        vm.initialize = initialize;

        vm.input = 625;
        vm.hidden = 300;
        vm.output = 4;

        function prepareWeightsValue() {
            var weights,
                i, j,
                numberRowWeights,
                numberColWeight = 4,
                count = 0;

            vm.weights.length = 0;

            weights = RecognitionService.getWeights();

            numberRowWeights = Math.ceil(weights.length / numberColWeight);

            for (i = 0; i < numberRowWeights; i++) {
                vm.weights[i] = [];
                for (j = 0; j < numberColWeight; j++) {
                    if (weights[count]) {
                        vm.weights[i][j] = weights[count];
                    }
                    count++;
                }
            }
        }

        function initialize() {
            RecognitionService.initialize(vm.input, vm.hidden, vm.output);
            ImageService.initialize(vm.output);

            prepareWeightsValue();
        }

        vm.matrixValuesY = RecognitionService.getMatrixValuesY();
        vm.matrixWeights = RecognitionService.getMatrixWeights();

        vm.tableConnection = RecognitionService.getMatrixCompounds();
        vm.uploadImages = uploadImages;
        vm.selectImage = selectImage;

        vm.train = function () {
            var image = ImageService.selectedImage.image,
                weights = RecognitionService.getWeights();

            if (_.isEmpty(image)) {
                alert('Не выбрано изображение для обработки');
            } else {
                if (_.isEmpty(weights[image.index].class)) {
                    alert('Отнесите выбранное изображение к какому-либо классу')
                } else {
                    var result = RecognitionService.train(image.matrix, 1, image.index, 1);

                    vm.weights.forEach(function (row) {
                        row.forEach(function (weight) {
                            console.log(weight);
                            if (weight.index == result.index) {
                                weight.right = result.right;
                            }
                        });
                    });
                }
            }
        };

        vm.automaticTraining = function () {
            var numberAllImages = 0, numberCorrectAnswers = 0,
                imageSelected, result, i, j, learnRate = 10000;

            vm.images.forEach(function (images) {
                images.forEach(function (image) {
                    numberAllImages++;
                });
            });


            while (numberCorrectAnswers != numberAllImages && learnRate >= 0) {
                numberCorrectAnswers = 0;

                vm.images.forEach(function (images, index) {
                    images.forEach(function (image) {
                        ImageService.selectImage(image, index);

                        imageSelected = ImageService.selectedImage.image;
                        result = RecognitionService.train(imageSelected.matrix, 1, imageSelected.index, 1);

                        vm.weights.forEach(function (row) {
                            row.forEach(function (weight) {
                                if (weight.index == result.index) {
                                    weight.right = result.right;
                                }
                            });
                        });

                        if (result.right) {
                            numberCorrectAnswers++;
                        } else {
                            numberCorrectAnswers--;
                        }
                    });
                });
                console.log(numberCorrectAnswers + '!=' + numberAllImages);

                learnRate--;
            }
        };

        vm.uploadImageForRecognition = function (images) {
            if (images && images.length) {
                for (var i = 0; i < images.length; i++) {
                    var image = images[i];

                    $upload.upload({
                        url: 'api/upload',
                        file: image
                    }).success(function (response) {
                        response.matrix = ImageService.convertTwoDimensionalMatrixToSingleMatrix(response.matrix);

                        vm.imageForRecognition = response;
                    });
                }
            }
        };

        vm.recognition = function () {
            var max, index, rec,
                weights = RecognitionService.getWeights();

            rec = RecognitionService.recognition(vm.imageForRecognition.matrix, 1);

            max = _.max(rec);
            index = _.findIndex(rec, function (value) {
                return value == max;
            });

            vm.response = weights[index].class;
        };

        function uploadImages(images, index) {
            ImageService.uploadImages(images, index);
        }

        function selectImage(image, index) {
            ImageService.selectImage(image, index);
        }

        vm.loadData = function () {
            var modalInstance = $modal.open({
                templateUrl: './js/app/recognition/modal.html',
                controller: 'ModalController',
                controllerAs: 'vm',
                size: 'md',
                resolve: {
                    data: function () {
                        return $http.get('api/getData');
                    }
                }
            });

            modalInstance.result.then(function (item) {
                vm.input = item.input;
                vm.hidden = item.hidden;
                vm.output = item.output;
                RecognitionService.setCountNeurons(vm.input, vm.hidden, vm.output);

                RecognitionService.setMatrixCompounds(item.compounds);

                RecognitionService.setWeights(item.weights);
                prepareWeightsValue();

                RecognitionService.setWeightsSum(item.weightsSum);
                RecognitionService.setMatrixValuesY(item.valuesY);

                ImageService.setImages(item.images);

                item.images.forEach(function (classes) {
                    classes.forEach(function (image) {
                        if (image.select) {
                            vm.selectedImage.image = image;
                        }
                    });
                });

            }, function () {
                $log.info('Modal dismissed at: ' + new Date());
            })
        };

        vm.saveData = function () {
            var data = {
                input: vm.input,
                hidden: vm.hidden,
                output: vm.output,
                weights: RecognitionService.getWeights(),
                weightsSum: RecognitionService.getWeightsSum(),
                valuesY: RecognitionService.getMatrixValuesY(),
                compounds: RecognitionService.getMatrixCompounds(),
                images: ImageService.images,
                title: vm.titleModel
            };

            $http.post('api/saveData', data).then(function (response) {
                console.log(response);
            });
        };

        vm.showMatrixCompounds = function () {
            $modal.open({
                templateUrl: './js/app/recognition/modal/modal.compounds.html',
                controller: 'ModalCompoundsController',
                controllerAs: 'vm',
                size: 'lg',
                resolve: {
                    compounds: function () {
                        return vm.tableConnection;
                    },
                    selectedImage: function () {
                        return vm.selectedImage;
                    }
                }
            });
        };

        vm.showMatrixWeightsSum = function () {
            $modal.open({
                templateUrl: './js/app/recognition/modal/modal.weightsSum.html',
                controller: 'ModalWeightsSumController',
                controllerAs: 'vm',
                size: 'md',
                resolve: {
                    originWeights: function () {
                        return vm.originWeights;
                    },
                    weightsSum: function () {
                        return vm.weightsSum;
                    }
                }
            });
        };

        vm.showMatrixWeights = function (index) {
            $modal.open({
                templateUrl: './js/app/recognition/modal/modal.weights.html',
                controller: 'ModalWeightsController',
                controllerAs: 'vm',
                size: 'lg',
                resolve: {
                    data: function () {
                        return vm.originWeights[index];
                    }
                }
            });
        };

        vm.showMatrixValuesY = function () {
            $modal.open({
                templateUrl: './js/app/recognition/modal/modal.valuesY.html',
                controller: 'ModalValuesYController',
                controllerAs: 'vm',
                size: 'lg',
                resolve: {
                    valuesY: function () {
                        return RecognitionService.getMatrixValuesY();
                    }
                }
            });
        };

        vm.showMainModal = function () {
            $modal.open({
                templateUrl: './js/app/recognition/modal/modal.main.html',
                controller: 'ModalMainController',
                controllerAs: 'vm',
                size: 'lg',
                resolve: {
                    valuesY: function () {
                        return [];
                    }
                }
            });
        };

        vm.imagesIndex = 0;

        vm.getImagesIndex = function () {
            return vm.imagesIndex++;
        }
    }
})();