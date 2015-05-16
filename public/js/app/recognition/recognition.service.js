(function () {
    'use strict';

    angular.module('app.recognition')
        .service('RecognitionService', RecognitionService);

    RecognitionService.$inject = ['_'];

    function RecognitionService(_) {
        var self = this,
            matrixCompounds = [], // Матрица соединений
            sumInputSignalsEachIterations = [], // Суммы всех входных сигналов каждой итерации
            matrixValuesY = [], // Матрица значений Y-ков
            matrixWeights = [], // Матрица весов

            weights = [],
            weightsSum = [];

        // ----------------------------------------------------------
        this.getMatrixCompounds = getMatrixCompounds;
        this.getMatrixValuesY = getMatrixValuesY;
        this.getWeights = getWeights;
        this.getWeightsSum = getWeightsSum;

        this.setMatrixCompounds = setMatrixCompounds;
        this.setWeights = setWeights;
        this.setWeightsSum = setWeightsSum;
        this.setMatrixValuesY = setMatrixValuesY;
        this.setCountNeurons = setCountNeurons;

        /**
         * Получить матрицу весов.
         * @returns {Array}
         */
        function getWeights() {
            return weights;
        }

        /**
         * Получить значения сумм весов.
         * @returns {Array}
         */
        function getWeightsSum() {
            return weightsSum;
        }

        /**
         * Возвращение матрицы связей.
         * @returns {Array}
         */
        function getMatrixCompounds() {
            return matrixCompounds;
        }

        /**
         * Возвращение матрицы значений Y-ков.
         * @returns {Array}
         */
        function getMatrixValuesY() {
            return matrixValuesY;
        }

        function setMatrixCompounds(compounds) {
            angular.extend(matrixCompounds, compounds);
        }

        function setWeights(saveWeights) {
            angular.extend(weights, saveWeights);
        }

        function setWeightsSum(saveWeightsSum) {
            angular.extend(weightsSum, saveWeightsSum);
        }

        function setMatrixValuesY(saveValuesY) {
            angular.extend(matrixValuesY, saveValuesY);
        }

        function setCountNeurons(numberInputNeurons, numberHiddenNeurons, numberOutputNeurons) {
            self.numberInputNeurons = numberInputNeurons;
            self.numberHiddenNeurons = numberHiddenNeurons;
            self.numberOutputNeurons = numberOutputNeurons;
        }
        // ----------------------------------------------------------

        this.numberInputNeurons = 0;
        this.numberHiddenNeurons = 0;
        this.numberOutputNeurons = 0;

        this.initialize = initialize;

        this.getMatrixWeights = getMatrixWeights;

        this.recognition = recognition;

        this.train = train;

        /**
         * Иницализация персептрона.
         * @param numberInputNeurons
         * @param numberHiddenNeurons
         * @param numberOutputNeurons
         */
        function initialize(numberInputNeurons, numberHiddenNeurons, numberOutputNeurons) {
            var i, output = [];

            self.numberInputNeurons = numberInputNeurons;
            self.numberHiddenNeurons = numberHiddenNeurons;
            self.numberOutputNeurons = numberOutputNeurons;

            weights.length = 0;

            for (i = 0; i < numberOutputNeurons; i++) {
                weights.push({
                    'class': i + 1,
                    'weights': [initializeMatrixWeights(numberHiddenNeurons, -1, 1)],
                    'output': initOutputValue(numberOutputNeurons, i),
                    'index': i,
                    'right': false
                });
            }

            generateMatrixCompounds(self.numberInputNeurons, self.numberHiddenNeurons);
        }

        /**
         * Инициализация ожидаемых ответов.
         * @param numberOutputValue
         * @returns {Array}
         * @param index
         */
        function initOutputValue(numberOutputValue, index) {
            var output = [], i;

            for (i = 0; i < numberOutputValue; i++) {
                output.push({
                    'value': 0
                });
            }

            output[index].value = 1;

            return output;
        }


        /**
         * Возвращение матрицы весов.
         * @returns {Array}
         */
        function getMatrixWeights() {
            return matrixWeights;
        }

        /**
         * Создание матрицы связей случайным образом.
         * @param countReceptor
         * @param countElementA
         */
        function generateMatrixCompounds(countReceptor, countElementA) {
            var i, valuesColumn,
                indexesEmptyColumns = [],   // Индексы пустых столбцоы
                indexesCrowdedColumns = [], // Индексы переполненных столбцов
                numberNotZeroValuesColumn;

            // Заполнение матрицы значениями
            for (i = 0; i < countReceptor; i++) {
                matrixCompounds[i] = generateRowMatrix(countElementA, 0, [-1, 1])
            }

            // Поиск пустых и переполненных столбцов для распределения значений связей
            for (i = 0; i < countElementA; i++) {
                valuesColumn = getValuesColumnMatrix(i);

                numberNotZeroValuesColumn = getNumberNonZeroValuesColumn(valuesColumn);

                if (numberNotZeroValuesColumn === 0) {
                    indexesEmptyColumns.push(i);
                } else if (numberNotZeroValuesColumn > 1) {
                    indexesCrowdedColumns.push(i);
                }
            }

            // Распределение значений между переполненными и пустыми столбцами
            fillEmptyColumns(indexesEmptyColumns, indexesCrowdedColumns);

            console.log('Индексы пустых столбцов:', indexesEmptyColumns);
            console.log('Индексы переполненных столбцов:', indexesCrowdedColumns);
        }

        /**
         * Генерация и заполнение строки значениями.
         *
         * @param rowLength
         * @param rowValue
         * @param randomValues
         * @returns {Array}
         */
        function generateRowMatrix(rowLength, rowValue, randomValues) {
            var row = [], i;

            for (i = 0; i < rowLength; i++) {
                row[i] = rowValue;
            }

            writeRandomValueToRow(row, randomValues);

            return row;
        }

        /**
         * Запись случайного значения из массива randomValues в строку.
         * @param row
         * @param randomValues
         */
        function writeRandomValueToRow(row, randomValues) {
            var rowLength = row.length,
                index = _.random(rowLength - 1),
                randomValuesLength = randomValues.length;

            row[index] = randomValues[_.random(randomValuesLength - 1)];
        }

        /**
         * Получение значений столбца матрицы.
         * @param indexColumn
         * @returns {Array}
         */
        function getValuesColumnMatrix(indexColumn) {
            var columnLength = matrixCompounds.length,
                valuesColumn = [], i;

            for (i = 0; i < columnLength; i++) {
                valuesColumn[i] = matrixCompounds[i][indexColumn];
            }

            return valuesColumn;
        }

        /**
         * Получение количества отличных от нуля значений в столбце.
         * @param valuesColumn
         * @returns {Number}
         */
        function getNumberNonZeroValuesColumn(valuesColumn) {
            var values;

            values = _.filter(valuesColumn, function (valueColumn) {
               return valueColumn;
            });

            return values.length;
        }

        /**
         * Получение отличных от нуля значений в столбце.
         * @param valuesColumn
         * @returns {Number}
         */
        function getNonZeroValuesColumn(valuesColumn) {
            var values;

            values = _.filter(valuesColumn, function (valueColumn) {
                return valueColumn;
            });

            return values;
        }

        /**
         * Получения индекса строки по совпадающему элементу.
         * @param valuesColumn
         * @param searchValue
         * @returns {number}
         */
        function getRowIndex(valuesColumn, searchValue) {
            var rowIndex;

            rowIndex = _.findIndex(valuesColumn, function (valueColumn) {
                return valueColumn === searchValue;
            });

            return rowIndex;
        }

        /**
         * Заполнение пустых столбцов значениями связи из переполненных столбцов.
         * @param indexesEmptyColumns
         * @param indexesFilledColumns
         */
        function fillEmptyColumns(indexesEmptyColumns, indexesFilledColumns) {
            var valuesColumn,
                notZeroValuesColumn,
                rowIndex, i,
                numberNotZeroValuesColumn,
                originalValueColumn;

            _.forEach(indexesFilledColumns).forEach(function (indexFilledColumn) {
                valuesColumn = getValuesColumnMatrix(indexFilledColumn);
                notZeroValuesColumn = getNonZeroValuesColumn(valuesColumn);
                numberNotZeroValuesColumn = notZeroValuesColumn.length;

                for (i = 0; i < numberNotZeroValuesColumn - 1; i++) {
                    rowIndex = getRowIndex(valuesColumn, notZeroValuesColumn[i]);

                    if (rowIndex !== -1) {
                        if (!indexesEmptyColumns.length) {
                            return false;
                        }

                        originalValueColumn = matrixCompounds[rowIndex][indexFilledColumn];

                        matrixCompounds[rowIndex][indexesEmptyColumns.shift()] = originalValueColumn;
                        matrixCompounds[rowIndex][indexFilledColumn] = valuesColumn[rowIndex] = 0;
                    }
                }
            });
        }

        /**
         * Вычисление значений Y-ков.
         * @param inputSignals
         * @param threshold
         * @returns {Array}
         */
        function evaluationValuesY(inputSignals, threshold) {
            var valuesY = [],
                sumAllInputSignals;

            // Если не передано значение порога, то устанавливаем значение в 0
            threshold = threshold || 0;

            // Получаем значения сумм
            sumAllInputSignals = _sumAllInputSignalsEachAElement(inputSignals);

            _.forEach(sumAllInputSignals, function (sum) {
                // Отнимаем пороговое значений от полученной суммы
                sum -= threshold;

                if (sum >= 0) {
                    valuesY.push(1);
                } else {
                    valuesY.push(0);
                }
            });

            // Заносим значения Y-ков в общий массив
            matrixValuesY.push(valuesY);

            return valuesY;
        }

        /**
         * Суммирование всех входных сигналов поступивших на каждый A-елемент.
         * @param inputSignals
         */
        function _sumAllInputSignalsEachAElement(inputSignals) {
            var sumAllInputSignalsEachAElement = [],
                columnAElement,
                numberAElement,
                numberInputSignals,
                i, j, sum = 0;

            // Получаем количество A-елементов
            numberAElement = matrixCompounds[0].length;

            // Получаем количество входных сигналов
            numberInputSignals = matrixCompounds.length;

            for (i = 0; i < numberAElement; i++) {
                // Получаем столбец со значениями связей A-го елемента
                columnAElement = getValuesColumnMatrix(i);

                for (j = 0; j < numberInputSignals; j++) {
                    sum += columnAElement[j] * inputSignals[j];
                }

                // Заносим значение суммы в массив
                sumAllInputSignalsEachAElement.push(sum);

                // Сбрасываем значение суммы до нуля для следующей итерации
                sum = 0;
            }

            // Заносим значения сумм в общий массив хранения всех сумм входных сигналов на каждой итерации
            sumInputSignalsEachIterations.push(sumAllInputSignalsEachAElement);

            return sumAllInputSignalsEachAElement;
        }

        /**
         * Инициализация весов первой строки случайным образом.
         */
        function initializeMatrixWeights(numberHiddenNeurons, min, max) {
            var weights = [], i;

            for (i = 0; i < numberHiddenNeurons; i++) {
                weights[i] = _.random(min, max);
            }

            return weights;
        }


        function increaseWeights(learningRate, indexWeights) {
            var valuesY, currentWeights, lengthValuesY, i;

            // Получаем значения активационных элементов для регулирования весов
            valuesY = matrixValuesY[matrixValuesY.length - 1];

            // Размерность активационных элементов
            lengthValuesY = valuesY.length;

            // Получаем значения весов для регулирования
            currentWeights = angular.copy(weights[indexWeights].weights[weights[indexWeights].weights.length - 1]);

            for (i = 0; i < lengthValuesY; i++) {
                if (valuesY[i]) {
                    currentWeights[i] += learningRate;
                }
            }

            weights[indexWeights].weights.push(currentWeights);
        }

        function reductionWeights(learningRate, indexWeights) {
            var valuesY, currentWeights, lengthValuesY, i;

            // Получаем значения активационных элементов для регулирования весов
            valuesY = matrixValuesY[matrixValuesY.length - 1];

            // Размерность активационных элементов
            lengthValuesY = valuesY.length;

            // Получаем значения весов для регулирования
            currentWeights = angular.copy(weights[indexWeights].weights[weights[indexWeights].weights.length - 1]);

            for (i = 0; i < lengthValuesY; i++) {
                if (valuesY[i]) {
                    currentWeights[i] -= learningRate;
                }
            }

            weights[indexWeights].weights.push(currentWeights);
        }

        function adjustWeights(actual, expected, learningRate) {
            var i;

            expected = expected.map(function (out) {
                return +out.value;
            });

            // Если персептрон не правильно распознал образ - корректируем веса
            if (!angular.equals(expected, actual)) {
                for (i = 0; i < self.numberOutputNeurons; i++) {
                    // Увеличиваем коэффиценты возбуждённых А-элементов
                    if (expected[i] == 1) {
                        if (actual[i] == 0) {
                            increaseWeights(learningRate, i);
                        }
                    }

                    // Уменьшаем коэффиценты возбуждённых А-элементов
                    if (expected[i] == 0) {
                        if (actual[i] == 1) {
                            reductionWeights(learningRate, i);
                        }
                    }
                }
            }
        }

        function train(input, threshold, indexWeight, learningRate) {
            var outputSignalsAElements, expected,
                currentWeights, i, j, sum = [], outputSignalsLength, maxSum, indexMaxSum;

            outputSignalsAElements = evaluationValuesY(input, threshold);
            outputSignalsLength = outputSignalsAElements.length;

            for (j = 0; j < self.numberOutputNeurons; j++) {
                currentWeights = weights[j].weights[weights[j].weights.length - 1];

                sum[j] = 0;
                for (i = 0; i < outputSignalsLength; i++) {
                    sum[j] += (outputSignalsAElements[i] * currentWeights[i]);
                }
            }

            // Сохраняем значения весов
            weightsSum.push(angular.copy(sum));

            maxSum = _.max(sum);
            indexMaxSum = _.indexOf(sum, maxSum);
            _.fill(sum, 0);
            sum[indexMaxSum] = 1;

            adjustWeights(sum, weights[indexWeight].output, learningRate, matrixValuesY.length - 1, matrixWeights.length - 1);

            expected = weights[indexWeight].output.map(function (out) {
                return +out.value;
            });

            console.log(sum);

            return {'right': angular.equals(sum, expected), 'index': weights[indexWeight].index};
        }

        function recognition(input, threshold) {
            var outputSignalsAElements,
                currentWeights, i, j, sum = [], outputSignalsLength;

            outputSignalsAElements = evaluationValuesY(input, threshold);
            outputSignalsLength = outputSignalsAElements.length;

            for (j = 0; j < self.numberOutputNeurons; j++) {
                currentWeights = weights[j].weights[weights[j].weights.length - 1];

                sum[j] = 0;
                for (i = 0; i < outputSignalsLength; i++) {
                    sum[j] += (outputSignalsAElements[i] * currentWeights[i]);
                }
            }

            return sum;
        }
    }
})();
