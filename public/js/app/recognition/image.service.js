(function () {
    'use strict';

    angular.module('app.recognition')
        .service('ImageService', ImageService);

    ImageService.$inject = ['_', '$upload'];

    function ImageService(_, $upload) {
        var self = this;

        this.initialize = initialize;

        this.images = [];
        this.selectedImage = {
            'image': null
        };

        this.uploadImages = uploadImages;
        this.selectImage = selectImage;
        this.setImages = setImages;
        this.convertTwoDimensionalMatrixToSingleMatrix = convertTwoDimensionalMatrixToSingleMatrix;

        /**
         * Инициализация массивов изображений для каждого класса.
         * @param numberClasses
         */
        function initialize(numberClasses) {
            var i;

            for (i = 0; i < numberClasses; i++) {
                self.images[i] = [];
            }
        }

        function setImages(saveImages) {
            angular.extend(self.images, saveImages);
        }

        /**
         * Загрузка фотографий.
         * @param images
         * @param index
         */
        function uploadImages(images, index) {
            if (images && images.length) {
                for (var i = 0; i < images.length; i++) {
                    var image = images[i];

                    $upload.upload({
                        url: 'api/upload',
                        file: image
                    }).success(function (response) {
                        response.select = false;
                        response.matrix = convertTwoDimensionalMatrixToSingleMatrix(response.matrix);

                        self.images[index].push(response);
                    });
                }
            }
        }

        /**
         * Выбор одного изображения для дальнейшей работы с ним.
         * @param image
 * @param index
         */
        function selectImage(image, index) {
            if (image.select) {
                image.select = false;
                self.selectedImage.image = null;
            } else {
                if (self.selectedImage.image) {
                    self.selectedImage.image.select = false;
                    self.selectedImage.image = null;
                }
                image.select = true;
                self.selectedImage.image = image;
                self.selectedImage.image.index = index;
            }
        }

        /**
         * Преобразование двумерного массива в одномерный
         * @param matrix
         * @returns {Array}
         */
        function convertTwoDimensionalMatrixToSingleMatrix(matrix) {
            var singleMatrix = [], width, height, i, j;

            width = matrix[0].length;
            height = matrix.length;

            for (i = 0; i < height; i++) {
                for (j = 0; j < width; j++) {
                    singleMatrix.push(matrix[i][j]);
                }
            }

            return singleMatrix;
        }
    }
})();