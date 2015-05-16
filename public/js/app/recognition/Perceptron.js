(function () {
    'use strict';

    angular.module('app.recognition')
        .service('PerceptronFactory', PerceptronFactory);

    PerceptronFactory.$inject = ['_', '$upload'];

    function PerceptronFactory(_, $upload) {
        var Perceptron = function () {
            this.connections = [];
            this.weights = [];

        };

        Perceptron.prototype = {
            initialize: function () {
                // convert arguments to Array
                var args = Array.prototype.slice.call(arguments);

                if (args.length < 3)
                    throw "Error: not enough layers (minimum 3) !!";

                this.numberInputNeurons = args[0];
                this.numberHiddenNeurons = args[1];
                this.numberOutputNeurons = args[2];
            }
        };

        return new Perceptron();
    }
})();