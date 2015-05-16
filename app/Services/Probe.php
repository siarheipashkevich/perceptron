<?php namespace App\Services;

/**
 * Класс для работы с зондами
 *
 * @package App\Services
 */
class Probe
{
    /**
     * Все найденные зонды
     *
     * @var array
     */
    protected static $probes = array();

    /**
     * Временный массив для определения координат зонда
     *
     * @var array
     */
    protected static $tempProbe;

    /**
     * Поиск всех зондов на изображении и возвращение данных о найденных зондах
     *
     * @param $matrix
     * @return array
     */
    public static function searchProbes($matrix)
    {
        // Получаем массив заполненный нулями
        $zeroMatrix = self::fillBoundaryZeros($matrix);

        // Результирующая матрица для работы
        $extendedMatrix = self::copyMatrix($zeroMatrix, $matrix);

        $width = count($extendedMatrix);
        $height = count($extendedMatrix[0]);

        for ($i = 1; $i < $width - 1; $i++) {
            for ($j = 1; $j < $height - 1; $j++) {
                if ($extendedMatrix[$i][$j] == 1) {
                    self::$tempProbe = array();

                    // Определяем линию зонда
                    self::defineLineProbe($extendedMatrix, $i, $j);

                    self::$probes[] = self::$tempProbe;
                }
            }
        }

        return self::$probes;
    }

    /**
     * Определяем линию зонда рекурсивным методом
     *
     * @param $matrix
     * @param $i
     * @param $j
     */
    protected static function defineLineProbe(&$matrix, $i, $j)
    {
        $p[0] = array(
            'value' => $matrix[$i - 1][$j],
            'coordinate' => array(
                'x' => $j,
                'y' => $i - 1
            )
        );
        $p[1] = array(
            'value' => $matrix[$i - 1][$j + 1],
            'coordinate' => array(
                'x' => $j + 1,
                'y' => $i - 1
            )
        );
        $p[2] = array(
            'value' => $matrix[$i][$j + 1],
            'coordinate' => array(
                'x' => $j + 1,
                'y' => $i
            )
        );
        $p[3] = array(
            'value' => $matrix[$i + 1][$j + 1],
            'coordinate' => array(
                'x' => $j + 1,
                'y' => $i + 1
            )
        );
        $p[4] = array(
            'value' => $matrix[$i + 1][$j],
            'coordinate' => array(
                'x' => $j,
                'y' => $i + 1
            )
        );
        $p[5] = array(
            'value' => $matrix[$i + 1][$j - 1],
            'coordinate' => array(
                'x' => $j - 1,
                'y' => $i + 1
            )
        );
        $p[6] = array(
            'value' => $matrix[$i][$j - 1],
            'coordinate' => array(
                'x' => $j - 1,
                'y' => $i
            )
        );
        $p[7] = array(
            'value' => $matrix[$i - 1][$j - 1],
            'coordinate' => array(
                'x' => $j - 1,
                'y' => $i - 1
            )
        );

        for ($k = 0; $k < 8; $k++) {
            if ($p[$k]['value'] == 1) {
                // Записываем координаты пикселя в массив
                self::$tempProbe[] = $p[$k]['coordinate'];

                // Заполняем предыдущий пиксель белым цветом
                $matrix[$i][$j] = 0;

                // Рекурсивно определяем
                self::defineLineProbe($matrix, $p[$k]['coordinate']['y'], $p[$k]['coordinate']['x']);
            }
        }

        // Заполняем последний пиксель найденной линии зонда
        $matrix[$i][$j] = 0;
    }

    /**
     * Дополняем массив пикселей граничными нулями
     *
     * @param $matrix
     * @return array
     */
    protected static function fillBoundaryZeros($matrix)
    {
        $newWidth = count($matrix) + 2;
        $newHeight = count($matrix[0]) + 2;

        $newMatrix = array();

        for ($i = 0; $i < $newWidth; $i++) {
            for ($j = 0; $j < $newHeight; $j++) {
                $newMatrix[$i][$j] = 0;
            }
        }

        return $newMatrix;
    }

    /**
     * Копирование массива в массив с граничными нулями
     *
     * @param $destinationMatrix
     * @param $initialMatrix
     * @return mixed
     */
    protected static function copyMatrix($destinationMatrix, $initialMatrix)
    {
        $width = count($initialMatrix);
        $height = count($initialMatrix[0]);

        for ($i = 0; $i < $width; $i++) {
            for ($j = 0; $j < $height; $j++) {
                $destinationMatrix[$i + 1][$j + 1] = $initialMatrix[$i][$j];
            }
        }

        return $destinationMatrix;
    }

    public static function defineIntersection($probes, $matrix)
    {
        $result = array();

        foreach ($probes as $key => $probe) {
            $lengthCoordinate = count($probe);

            $intersection = 0;

            for ($i = 0; $i < $lengthCoordinate; $i++) {
                if ($matrix[$probe[$i]['y']][$probe[$i]['x']] == 1) {
                    $intersection++;
                }
            }

            $result[] = $intersection;
        }

        return $result;
    }
}
