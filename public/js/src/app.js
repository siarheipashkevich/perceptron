'use strict';

var app = angular.module('App', [
    'angularFileUpload'
], function ($interpolateProvider) {
    $interpolateProvider.startSymbol('<%');
    $interpolateProvider.endSymbol('%>');
});

app.controller('AppController', function ($scope, $upload) {
    $scope.images = {};

    $scope.results = [];

    $scope.uploadImage = function (files, event, imageNumber) {
        var params = {
            probe: $scope.probe
        };

        $scope.defaults = [
            {
                letter: 'а',
                class: 'Класс 1',
                intersections: [1, 1, 1]
            },
            {
                letter: 'б',
                class: 'Класс 2',
                intersections: [2, 0, 1]
            },
            {
                letter: 'в',
                class: 'Класс 3',
                intersections: [2, 2, 1]
            }
        ];

        if (files && files.length) {
            $upload.upload({
                url: 'image/upload',
                fields: params,
                file: files[0]
            }).success(function (data) {
                if (data.error.length) {
                    angular.forEach(data.error, function (value, key) {
                        alertify.error(value);
                    });
                } else {
                    $scope.images[imageNumber] = data.data;

                    var nameImage;

                    switch (imageNumber) {
                        case 'one':
                            nameImage = 'Первое';
                            break;

                        case 'two':
                            nameImage = 'Второе';
                            break;

                        case 'three':
                            nameImage = 'Третье';
                            break;
                    }

                    $scope.results.push({
                        name: nameImage,
                        probes: data.data.intersections
                    });

                    $scope.results[$scope.results.length - 1].class = '-';
                    $scope.results[$scope.results.length - 1].letter = '-';

                    angular.forEach($scope.defaults, function (value) {
                        if (angular.equals(value.intersections, $scope.results[$scope.results.length - 1].probes)) {
                            $scope.results[$scope.results.length - 1].class = value.class;
                            $scope.results[$scope.results.length - 1].letter = value.letter;
                        }
                    });
                }
            });
        }
    };

    $scope.uploadProbes = function (files) {
        var params = {};

        if (files && files.length) {
            $upload.upload({
                url: 'probe/upload',
                fields: params,
                file: files[0]
            }).success(function (data) {
                $scope.probe = data;
            });
        }
    };
});

app.controller('RecController', function ($scope, $upload, $http) {
    this.init = function init() {
        $scope.sectionClasses = [
            {
                "typeClass": 1,
                "btn": 'Загрузить изображения для 1-го класса'
            },
            {
                "typeClass": 2,
                "btn": 'Загрузить изображения для 2-го класса'
            },
            {
                "typeClass": 3,
                "btn": 'Загрузить изображения для 3-го класса'
            },
            {
                "typeClass": 4,
                "btn": 'Загрузить изображения для 4-го класса'
            }
        ];

        // Объект для классов изображений
        $scope.classes = {};

        // Изображение для распознования
        $scope.recognitionImage = {};

        // Значения для таблицы результатов метода ближайших соседей и метода ближайшего соседа
        $scope.tableNearestNeighbor = [];
        $scope.tableNearestNeighbors = [];
    };

    $scope.isEmpty = function isEmpty(obj) {
        for (var key in obj) {
            return false; // если цикл хоть раз сработал, то объект не пустой => false
        }
        // дошли до этой строки - значит цикл не нашёл ни одного свойства => true
        return true;
    };


    /* init */
    this.init();

    $scope.uploadImages = function (files, typeClass) {
        var params = {};

        // Инициализируем тип класса
        if (angular.isUndefined($scope.classes[typeClass])) {
            $scope.classes[typeClass] = {
                color: [],
                grayscale: []
            };
        }

        if (files && files.length) {
            for (var i = 0; i < files.length; i++) {
                var file = files[i];

                $upload.upload({
                    url: 'recognition/upload',
                    fields: params,
                    file: file
                }).success(function (data) {
                    $scope.classes[typeClass].color.push({
                        path: data.path,
                        typeClass: typeClass,
                        matrix: data.matrix
                    });
                });
            }
        }
    };

    $scope.convertColorToGrayscale = function (typeClass) {
        $http.post('recognition/convert', {images: $scope.classes[typeClass].color})
            .success(function (response) {
                angular.forEach(response, function (image) {
                    $scope.classes[typeClass].grayscale.push(image);
                });
            });
    };

    // Распозноваемое изображение
    $scope.uploadImageForRecognition = function (files) {
        var params = {};

        if (files && files.length) {
            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                $upload.upload({
                    url: 'recognition/upload',
                    fields: params,
                    file: file
                }).success(function (data) {
                    $scope.recognitionImage.color = data;
                });
            }
        }
    };

    $scope.convertColorToGrayscaleRecognitionImage = function () {
        $http.post('recognition/convert', {images: [$scope.recognitionImage.color]})
            .success(function (data) {
                $scope.recognitionImage.grayscale = data[0];
            });
    };

    $scope.convertGrayscaleToBinary = function convertGrayscaleToBinary() {
        var prepareArrayMatrix = [];

        angular.forEach($scope.classes, function (imageClass) {
            for (var i = 0; i < imageClass.grayscale.length; i++) {
                this.push(imageClass.grayscale[i]);
            }
        }, prepareArrayMatrix);

        $http.post('recognition/binary', {
            images: prepareArrayMatrix,
            threshold: $scope.threshold
        }).success(function (response) {
            $scope.binaryImages = response;
        });

        $http.post('recognition/binary', {
            images: [$scope.recognitionImage.grayscale],
            threshold: $scope.threshold
        }).success(function (response) {
            $scope.recognitionImage.binary = response[0];
        });
    };

    $scope.nearestNeighbors = function nearestNeighbor () {
        var recognitionMatrixImage = $scope.recognitionImage.grayscale;
        var prepareArrayMatrix = [];

        angular.forEach($scope.classes, function (imageClass) {
            for (var i = 0; i < imageClass.grayscale.length; i++) {
                this.push(imageClass.grayscale[i]);
            }
        }, prepareArrayMatrix);

        $http.post('recognition/recognition', {
            initialMatrixImages: prepareArrayMatrix,
            recognitionMatrixImage: recognitionMatrixImage
        }).success(function (response) {
            $scope.tableNearestNeighbors = response;
        });
    };

    $scope.nearestNeighbor = function nearestNeighbor() {
        $http.post('recognition/recognition', {
            initialMatrixImages: $scope.binaryImages,
            recognitionMatrixImage: $scope.recognitionImage.binary
        }).success(function (response) {
            $scope.tableNearestNeighbor = response;
        });
        console.log($scope.threshold);
    };

    $scope.getLengthRow = function getLengthRow() {
        if (!$scope.recognitionImage.grayscale) return;

        var lengthRow = $scope.recognitionImage.grayscale.matrix.length * $scope.recognitionImage.grayscale.matrix.length;

        return new Array(lengthRow);
    };
});

angular.element(document).ready(function() {
    angular.bootstrap(document, ['App']);
});
