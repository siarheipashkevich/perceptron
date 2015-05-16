<?php namespace App\Services;

/**
 * Класс для работы с изображением
 *
 * @package App\Services
 */
class Image
{
    /**
     * Пороговое значение
     */
    const THRESHOLD = 128;

    /**
     * Загрузка изображения
     *
     * @param $photo
     * @return array|bool
     */
    public static function upload($photo)
    {
        if (isset($photo)) {
            $name = $photo['name'];
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $uniqueName = sha1($name . time()) . "." . $extension;
            $path = "image/" . $uniqueName;

            if (move_uploaded_file($photo['tmp_name'], $path)) {
                return $path;
            } else {
                return false;
            }
        }

        return false;
    }

    /**
     * Получение инофрмации о изображении
     *
     * @param $pathImage
     * @return array
     */
    public static function getInformation($pathImage)
    {
        $imageSize = getimagesize($pathImage);
        $width = $imageSize[0];
        $height = $imageSize[1];

        $extension = strtolower(pathinfo($pathImage, PATHINFO_EXTENSION));

        return [
            'width' => $width,
            'height' => $height,
            'extension' => $extension
        ];
    }

    /**
     * Получение ресурса изображения переданного в параметре $pathImage
     *
     * @param $pathImage
     * @return bool|null|resource
     */
    public static function getResource($pathImage)
    {
        $extension = strtolower(pathinfo($pathImage, PATHINFO_EXTENSION));

        switch ($extension) {
            case 'bmp':
                $image = BMP::imagecreatefrombmp($pathImage);
                break;
            case 'jpg':
                $image = imagecreatefromjpeg($pathImage);
                break;
            default:
                $image = null;
        }

        return $image;
    }

    /**
     * Получение матрицы пикселей
     *
     * @param $pathImage
     * @param $type
     * @return array
     * @internal param $pathPhoto
     */
    public static function getMatrix($pathImage, $type)
    {
        $imageSize = getimagesize($pathImage);
        $width = $imageSize[0];
        $height = $imageSize[1];

        // Получение ресурса изображения для получения матрицы пикселей
        $image = Image::getResource($pathImage);

        // Матрица пикселей изображений
        $matrix = array();

        if ($image) {
            for ($i = 0; $i < $height; $i++) {
                $stringX = array();

                for ($j = 0; $j < $width; $j++) {
                    $rgb = imagecolorat($image, $j, $i);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;

                    switch ($type) {
                        case 'binary':
                            if ($r < 50 && $g < 50 && $b < 50) {
                                array_push($stringX, 1);
                            } else {
                                array_push($stringX, 0);
                            }
                            break;

                        case 'grayscale':
                            array_push($stringX, $r);
                            break;

                        case 'color':
                            array_push($stringX, [
                                'r' => $r,
                                'g' => $g,
                                'b' => $b
                            ]);
                    }
                }
                array_push($matrix, $stringX);

                unset($stringX);
            }
        }

        return $matrix;
    }

    /**
     * Поиск крайних точек в матрице пикселей для центрирования изображения
     *
     * @param $matrix
     * @return array|bool
     */
    public static function searchExtremePoints($matrix)
    {
        if (!empty($matrix) && count($matrix[0])) {
            // Получаем ширину и высоту матрицы
            $width = count($matrix[0]);
            $height = count($matrix);

            // Координаты X и Y изображения
            $coordinateX = array();
            $coordinateY = array();

            // Записываем координаты чёрных пикселей (изображения)
            for ($i = 0; $i < $height; $i++) {
                for ($j = 0; $j < $width; $j++) {
                    if ($matrix[$i][$j] != 0) {
                        $coordinateX[] = $j;
                        $coordinateY[] = $i;
                    }
                }
            }

            $leftExtremePoint = min($coordinateX);
            $rightExtremePoint = max($coordinateX);
            $topExtremePoint = min($coordinateY);
            $bottomExtremePoint = max($coordinateY);

            return [
                'left' => $leftExtremePoint,
                'right' => $rightExtremePoint,
                'top' => $topExtremePoint,
                'bottom' => $bottomExtremePoint
            ];
        }

        return false;
    }

    /**
     * Выделяем значимую область изображения с символом
     *
     * @param $pathImage
     * @param $extremePoints
     * @return string
     */
    public static function getSignificantArea($pathImage, $extremePoints)
    {
        $infoImage = Image::getInformation($pathImage);

        // Получаем значимый размер области с изображением (ширину и высоту)
        $significantWidth = $extremePoints['right'] - $extremePoints['left'];
        $significantHeight = $extremePoints['bottom'] - $extremePoints['top'];

        // Имя выделенной области изображения
        $areaPathImage = 'image/area_' . sha1('area' . time()) . "." . $infoImage['extension'];

        // Получаем ресурс исходного изображения
        $resourceInitialImage = Image::getResource($pathImage);

        // Создаём новый ресурс для значимой области изображения
        $resourceAreaImage = imagecreatetruecolor($significantWidth + 5, $significantHeight + 5);

        // Заливаем фон нового изображения белым цветом
        $white = imagecolorallocate($resourceAreaImage, 255, 255, 255);
        imagefill($resourceAreaImage, 0, 0, $white);

        // Копируем область изображения с символом в новое изображение
        imagecopy(
            $resourceAreaImage,
            $resourceInitialImage,
            2,
            2,
            $extremePoints['left'],
            $extremePoints['top'],
            $significantWidth + 1,
            $significantHeight + 1
        );

        // Сохраняем значимую область изображение
        switch ($infoImage['extension']) {
            case 'jpg':
                imagejpeg($resourceAreaImage, $areaPathImage, 100);
                break;
            case 'bmp':
                BMP::imagebmp($resourceAreaImage, $areaPathImage);
                break;
        }

        // Освобождаем память
        imagedestroy($resourceAreaImage);

        return $areaPathImage;
    }

    /**
     * Масштабирование изображения под размер изображения с раположениями зондов
     *
     * @param $areaPathImage
     * @param $probePathImage
     * @return array
     */
    public static function scaling($areaPathImage, $probePathImage)
    {
        $result = [];

        if (!file_exists($areaPathImage)) {
            $result['error'][] = 'Изображение с выделенным символом не найдено';
            return $result;
        }

        if (!file_exists($probePathImage)) {
            $result['error'][] = 'Изображение маски с зондами не найдено';
            return $result;
        }

        // Информация о изображении с выделенной областью
        $areaInfoImage = Image::getInformation($areaPathImage);

        // Информация о изображении с маской (зондами)
        $probeInfoImage = Image::getInformation($probePathImage);

        // Параметры для масштабирования изображения
        $paramsScaling = [
            'width' => $probeInfoImage['width'],
            'height' => $probeInfoImage['height'],
            'aspect_ratio' => true,
            'crop' => false
        ];

        // Имя масштабированного изображения
        $scalingPathImage = 'image/scaling_' . sha1('scaling' . time()) . "." . $areaInfoImage['extension'];

        // Масштабирование изображения
        $scalingCheck = Image::resize($areaPathImage, $scalingPathImage, $paramsScaling);

        if (!$scalingCheck) {
            $result['error'][] = 'Изображение не было масштабировано';

            return $result;
        }

        $result['path'] = $scalingPathImage;

        return $result;
    }

    /**
     * Центрирование значимой области изображения, если это необходимо
     *
     * @param $pathImage
     * @param $extremePoints
     */
    public static function centering($pathImage, $extremePoints)
    {
        $infoImage = Image::getInformation($pathImage);

        $diffLeft = $extremePoints['left'];
        $diffRight = $infoImage['width'] - $extremePoints['right'];
        $diffTop = $extremePoints['top'];
        $diffBottom = $infoImage['height'] - $extremePoints['bottom'];

        // Получаем значимый размер области с изображением (ширину и высоту)
        $significantWidth = $extremePoints['right'] - $extremePoints['left'];
        $significantHeight = $extremePoints['bottom'] - $extremePoints['top'];

        // Координаты для копирования изображения в новое изображение
        $coordinateX = ($infoImage['width'] - $significantWidth) / 2;
        $coordinateY = ($infoImage['height'] - $significantHeight) / 2;

        // Проверяем, нужно ли отцентрировать область с изображением по высоте и ширине
        $checkCenteredWidth = ($diffLeft != $diffRight);
        $checkCenteredHeight = ($diffTop != $diffBottom);

        if ($checkCenteredWidth || $checkCenteredHeight) {
            // Имя отцентрированного изображения
            $centeringPathImage = 'image/center_' . sha1('centering' . time()) . "." . $infoImage['extension'];

            // Получаем ресурс исходного изображения
            $resourceInitialImage = Image::getResource($pathImage);

            // Создаём новый ресурс для отцентрирования изображения
            $resourceCenteringImage = imagecreatetruecolor($infoImage['width'], $infoImage['height']);

            // Заливаем фон нового изображения белым цветом
            $white = imagecolorallocate($resourceCenteringImage, 255, 255, 255);
            imagefill($resourceCenteringImage, 0, 0, $white);

            // Копируем область изображения с символом в новое изображение
            imagecopy(
                $resourceCenteringImage,
                $resourceInitialImage,
                $coordinateX,
                $coordinateY,
                $extremePoints['left'],
                $extremePoints['top'],
                $significantWidth + 1,
                $significantHeight + 1
            );

            // Сохраняем отцентрированное изображение (временно)
            imagejpeg($resourceCenteringImage, $centeringPathImage, 100);

            // Освобождаем память
            imagedestroy($resourceCenteringImage);
        }
    }

    /**
     * Изменение размеров изображения
     *
     * @param string  $ini_path Path to initial image.
     * @param string $dest_path Path to save new image.
     * @param array $params [optional] Must be an associative array of params
     * $params['width'] int New image width.
     * $params['height'] int New image height.
     * $params['constraint'] array.$params['constraint']['width'], $params['constraint'][height]
     * If specified the $width and $height params will be ignored.
     * New image will be resized to specified value either by width or height.
     * $params['aspect_ratio'] bool If false new image will be stretched to specified values.
     * If true aspect ratio will be preserved an empty space filled with color $params['rgb']
     * It has no sense for $params['constraint'].
     * $params['crop'] bool If true new image will be cropped to fit specified dimensions. It has no sense for $params['constraint'].
     * $params['rgb'] Hex code of background color. Default 0xFFFFFF.
     * $params['quality'] int New image quality (0 - 100). Default 100.
     * @return bool True on success.
     */
    public static function resize($ini_path, $dest_path, $params = array())
    {
        $width = !empty($params['width']) ? $params['width'] : null;
        $height = !empty($params['height']) ? $params['height'] : null;
        $constraint = !empty($params['constraint']) ? $params['constraint'] : false;
        $rgb = !empty($params['rgb']) ?  $params['rgb'] : 0xFFFFFF;
        $quality = !empty($params['quality']) ?  $params['quality'] : 100;
        $aspect_ratio = isset($params['aspect_ratio']) ?  $params['aspect_ratio'] : true;
        $crop = isset($params['crop']) ?  $params['crop'] : true;

        if (!file_exists($ini_path)) return false;


        if (!is_dir($dir=dirname($dest_path))) mkdir($dir);

        $img_info = getimagesize($ini_path);
        if ($img_info === false) return false;

        $ini_p = $img_info[0]/$img_info[1];
        if ( $constraint ) {
            $con_p = $constraint['width']/$constraint['height'];
            $calc_p = $constraint['width']/$img_info[0];

            if ( $ini_p < $con_p ) {
                $height = $constraint['height'];
                $width = $height*$ini_p;
            } else {
                $width = $constraint['width'];
                $height = $img_info[1]*$calc_p;
            }
        } else {
            if ( !$width && $height ) {
                $width = ($height*$img_info[0])/$img_info[1];
            } else if ( !$height && $width ) {
                $height = ($width*$img_info[1])/$img_info[0];
            } else if ( !$height && !$width ) {
                $width = $img_info[0];
                $height = $img_info[1];
            }
        }

        preg_match('/\.([^\.]+)$/i',basename($dest_path), $match);
        $ext = $match[1];
        $output_format = ($ext == 'jpg') ? 'jpeg' : $ext;

        $format = strtolower(substr($img_info['mime'], strpos($img_info['mime'], '/')+1));
        $icfunc = "imagecreatefrom" . $format;

        $iresfunc = "image" . $output_format;

        if (!function_exists($icfunc)) return false;

        $dst_x = $dst_y = 0;
        $src_x = $src_y = 0;
        $res_p = $width/$height;
        if ( $crop && !$constraint ) {
            $dst_w  = $width;
            $dst_h = $height;
            if ( $ini_p > $res_p ) {
                $src_h = $img_info[1];
                $src_w = $img_info[1]*$res_p;
                $src_x = ($img_info[0] >= $src_w) ? floor(($img_info[0] - $src_w) / 2) : $src_w;
            } else {
                $src_w = $img_info[0];
                $src_h = $img_info[0]/$res_p;
                $src_y    = ($img_info[1] >= $src_h) ? floor(($img_info[1] - $src_h) / 2) : $src_h;
            }
        } else {
            if ( $ini_p > $res_p ) {
                $dst_w = $width;
                $dst_h = $aspect_ratio ? floor($dst_w/$img_info[0]*$img_info[1]) : $height;
                $dst_y = $aspect_ratio ? floor(($height-$dst_h)/2) : 0;
            } else {
                $dst_h = $height;
                $dst_w = $aspect_ratio ? floor($dst_h/$img_info[1]*$img_info[0]) : $width;
                $dst_x = $aspect_ratio ? floor(($width-$dst_w)/2) : 0;
            }
            $src_w = $img_info[0];
            $src_h = $img_info[1];
        }

        $isrc = $icfunc($ini_path);
        $idest = imagecreatetruecolor($width, $height);
        if ( ($format == 'png' || $format == 'gif') && $output_format == $format ) {
            imagealphablending($idest, false);
            imagesavealpha($idest,true);
            imagefill($idest, 0, 0, IMG_COLOR_TRANSPARENT);
            imagealphablending($isrc, true);
            $quality = 0;
        } else {
            imagefill($idest, 0, 0, $rgb);
        }
        imagecopyresampled($idest, $isrc, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
        $res = $iresfunc($idest, $dest_path, $quality);

        imagedestroy($isrc);
        imagedestroy($idest);

        return $res;
    }

    /**
     * Глобальная бинаризация изображения (пороговая)
     *
     * @param $pathImage
     * @param null $threshold
     * @param array $grayMatrix
     * @return array
     */
    public static function thresholdBinarization($pathImage, $threshold = null, $grayMatrix = array())
    {
        $binarization = array();

        $threshold = $threshold ?: self::THRESHOLD;

        if (!empty($grayMatrix)) {
            $matrix = $grayMatrix;
        } else {
            $matrix = self::getMatrix($pathImage, 'grayscale');
        }

        $width = count($matrix);
        $height = count($matrix[0]);

        for ($i = 0; $i < $width; $i++) {
            for ($j = 0; $j < $height; $j++) {
                if ($matrix[$i][$j] < $threshold) {
                    $binarization[$i][$j] = 1;
                } else {
                    $binarization[$i][$j] = 0;
                }
            }
        }

        return $binarization;
    }

    /**
     * Создание изображения по матрице бинарных значений
     *
     * @param $matrix
     * @param $nameImage
     * @return mixed
     */
    public static function createImageByMatrix($matrix, $nameImage)
    {
        // Массив с цыетовой палитрой для перевода бинарных чисел в rgb
        $rgb = [255, 0];

        // Получаем размеры изображения
        $width = count($matrix);
        $height = count($matrix[0]);

        // Создаём ресурс для создаваемого изображения
        $im = imagecreatetruecolor($height, $width);

        for ($i = 0; $i < $width; $i++) {
            for ($j = 0; $j < $height; $j++) {
                // Интесивность пикселя
                $intensity = $rgb[$matrix[$i][$j]];

                $color = imagecolorallocate($im, $intensity, $intensity, $intensity);

                imagesetpixel($im, $j, $i, $color);
            }
        }

        imagejpeg($im, $nameImage, 100);

        return $nameImage;
    }

    public static function createImageByMatrix1($matrix, $extension)
    {
        // Массив с цыетовой палитрой для перевода бинарных чисел в rgb
        $rgb = [255, 0];

        $uniqueName = sha1('convertGrayscale' . microtime()) . "." . $extension;
        $path = "image/" . $uniqueName;

        // Получаем размеры изображения
        $width = count($matrix);
        $height = count($matrix[0]);

        // Создаём ресурс для создаваемого изображения
        $im = imagecreatetruecolor($height, $width);

        for ($i = 0; $i < $width; $i++) {
            for ($j = 0; $j < $height; $j++) {
                // Интесивность пикселя
                $intensity = $rgb[$matrix[$i][$j]];

                $color = imagecolorallocate($im, $intensity, $intensity, $intensity);

                imagesetpixel($im, $j, $i, $color);
            }
        }

        imagejpeg($im, $path, 100);

        return $path;
    }

    public static function createGrayscaleByMatrix($matrix, $extension)
    {
        $width = count($matrix[0]);
        $height = count($matrix);

        $uniqueName = sha1('convertGrayscale' . microtime()) . "." . $extension;
        $path = "image/" . $uniqueName;

        // Создаём ресурс для создаваемого изображения
        $im = imagecreatetruecolor($width, $height);

        for ($i = 0; $i < $height; $i++) {
            for ($j = 0; $j < $width; $j++) {
                // Интесивность пикселя
                $intensity = $matrix[$i][$j];

                $color = imagecolorallocate($im, $intensity, $intensity, $intensity);

                imagesetpixel($im, $j, $i, $color);
            }
        }

        imagejpeg($im, $path, 100);

        return $path;
    }

    /**
     * Перевод rgb-матрицы цветов в полутоновое
     *
     * @param $matrix
     * @return array
     */
    public static function convertRgbToGrayscale($matrix)
    {
        $result = [];

        $height = count($matrix);
        $width = count($matrix[0]);

        for ($i = 0; $i < $height; $i++) {
            for ($j = 0; $j < $width; $j++) {
                $sumRgb = $matrix[$i][$j]['r'] + $matrix[$i][$j]['g'] + $matrix[$i][$j]['b'];
                $result[$i][$j] = round($sumRgb / 3);
            }
        }

        return $result;
    }

    protected static function eightNeighbors($image, $i, $j)
    {
        $p[0] = $image[$i - 1][$j];
        $p[1] = $image[$i - 1][$j + 1];
        $p[2] = $image[$i][$j + 1];
        $p[3] = $image[$i + 1][$j + 1];
        $p[4] = $image[$i + 1][$j];
        $p[5] = $image[$i + 1][$j - 1];
        $p[6] = $image[$i][$j - 1];
        $p[7] = $image[$i - 1][$j - 1];

        $count = 0;
        for ($k = 0; $k <= 7; $k++) {
            if ($p[$k] == 1) {
                $count++;
            }
        }

        return $count;
    }

    public static function convertGrayscaleToBinary($images, $threshold)
    {
        foreach ($images as &$image) {
            $width = count($image['matrix'][0]);
            $height = count($image['matrix']);

            $binaryMatrix = [];
            for ($i = 0; $i < $height; $i++) {
                for ($j = 0; $j < $width; $j++) {
                    if ($image['matrix'][$i][$j] > $threshold) {
                        $binaryMatrix[$i][$j] = 0;
                    } else {
                        $binaryMatrix[$i][$j] = 1;
                    }
                }
            }

            // Расширенная матрица (восемь соседей)
            $newWidth = $width + 2;
            $newHeight = $height + 2;

            $extendedMatrix = [];
            for ($i = 0; $i < $newHeight; $i++) {
                for ($j = 0; $j < $newWidth; $j++) {
                    $extendedMatrix[$i][$j] = 0;
                }
            }

            for ($i = 1; $i < $newHeight - 1; $i++) {
                for ($j = 1; $j < $newWidth - 1; $j++) {
                    $extendedMatrix[$i][$j] = $binaryMatrix[$i - 1][$j - 1];
                }
            }

            $resultMatrix = [];
            for ($i = 1; $i < $newHeight - 1; $i++) {
                for ($j = 1; $j < $newWidth - 1; $j++) {
                    $resultMatrix[$i - 1][$j - 1] = self::eightNeighbors($extendedMatrix, $i, $j);
                }
            }

            $image['matrix'] = $resultMatrix;
        }

        return $images;
    }

    public static function distanceEuclid($initial, $recognition)
    {
        $response = [];

        $calculatedR = [];

        foreach ($initial as &$initialClass) {
            $width = count($initialClass['matrix'][0]);
            $height = count($initialClass['matrix']);

            $r = 0;

            for ($i = 0; $i < $height; $i++) {
                for ($j = 0; $j < $width; $j++) {
                    $r += pow($initialClass['matrix'][$i][$j] - $recognition['matrix'][$i][$j], 2);
                }
            }

            $r = round(sqrt($r));
            array_push($calculatedR, $r);

            $initialClass['r'] = $r;
        }

        $maxR = max($calculatedR);

        $r = $maxR / 2;

        $suitableImages = [];
        foreach ($initial as $image) {
            if ($image['r'] < $r) {
                $suitableImages[] = $image;
            }
        }

        // Считаем количество изображений для каждого класса
        $forCountingImages = [];
        foreach ($suitableImages as $image) {
            $forCountingImages[$image['typeClass']][] = $image;
        }

        $countImagesEachClass = [];
        foreach ($forCountingImages as $typeClass => $countImages) {
            $countImagesEachClass[$typeClass] = count($countImages);
        }

        $copyCountImagesEachClass = $countImagesEachClass;
        $intersectCountImagesEachClass = array_intersect($countImagesEachClass, $copyCountImagesEachClass);

        if (!empty($intersectCountImagesEachClass)) {
            $imageWithR = [];

            foreach ($intersectCountImagesEachClass as $typeClass => $countImages) {
                $imageClass = $forCountingImages[$typeClass];

                foreach ($imageClass as $image) {
                    $imageWithR[] = $image['r'];
                }
            }

            $imageMinR = min($imageWithR);

            foreach ($intersectCountImagesEachClass as $typeClass => $countImages) {
                $imageClass = $forCountingImages[$typeClass];

                foreach ($imageClass as $image) {
                    if ($imageMinR == $image['r']) {
                        if (!isset($recognitionTypeClass)) {
                            $recognitionTypeClass = $image['typeClass'];
                        }
                    }
                }
            }

            foreach ($initial as &$image) {
                if ($image['typeClass'] == $recognitionTypeClass) {
                    $image['light'] = true;
                }

                $width = count($image['matrix'][0]);
                $height = count($image['matrix']);

                $matrixLine = [];
                for ($i = 0; $i < $height; $i++) {
                    for ($j = 0; $j < $width; $j++) {
                        $matrixLine[] = $image['matrix'][$i][$j];
                    }
                }

                $image['matrix'] = $matrixLine;
            }
        } else {
            foreach ($initial as &$image) {
                $width = count($image['matrix'][0]);
                $height = count($image['matrix']);

                $matrixLine = [];
                for ($i = 0; $i < $height; $i++) {
                    for ($j = 0; $j < $width; $j++) {
                        $matrixLine[] = $image['matrix'][$i][$j];
                    }
                }

                $image['matrix'] = $matrixLine;
            }
        }

        return $initial;
    }

    // ----------------------------------------------------------------------------------------------------------------

    /**
     * Получить интенсивность пикселей в строках.
     * @param $matrix
     * @param int $offsetPixels
     * @return array
     */
    public static function getIntensityPixelsLines($matrix, $offsetPixels = 0)
    {
        $intensity = [];

        foreach ($matrix as $string) {
            $numberPixels = 0;
            $stringLength = count($string);

            for ($i = $offsetPixels; $i < $stringLength; $i++) {
                if ($string[$i]) {
                    $numberPixels++;
                }
            }

            $intensity[] = $numberPixels;
        }

        return $intensity;
    }

    /**
     * Получить интенсивность пикселей в столбцах.
     * @param $matrix
     * @param int $offsetPixels
     * @return array
     */
    public static function getIntensityPixelsColumns($matrix, $offsetPixels = 0)
    {
        $intensity = [];

        $numberStrings = count($matrix);
        $numberColumns = count($matrix[0]);

        for ($i = 0; $i < $numberColumns; $i++) {
            $numberPixels = 0;

            for ($j = 0; $j < $numberStrings; $j++) {
                if ($matrix[$j][$i]) {
                    $numberPixels++;
                }
            }

            $intensity[] = $numberPixels;
        }

        return $intensity;
    }

    /**
     * Поиск черных областей и их координат.
     * @param array $intensityPixels
     * @return array
     */
    public static function searchBlackAreas($intensityPixels)
    {
        $foundBlackAreas = [];
        $numberElements = count($intensityPixels);

        $fromWhiteToBlack = 0;
        for ($i = 0; $i < $numberElements - 1; $i++) {
            // Переход с белого на чёрное
            if (!$intensityPixels[$i] && $intensityPixels[$i + 1]) {
                $fromWhiteToBlack = $i + 1;
            }

            // Переход с белого на чёрное
            if ($intensityPixels[$i] && !$intensityPixels[$i + 1]) {
                $fromBackToWhite = $i + 1;
                $middle = $fromBackToWhite - (round(($fromBackToWhite - $fromWhiteToBlack) / 2));

                $foundBlackAreas[] = [
                    'start' => $fromWhiteToBlack,
                    'middle' => $middle,
                    'end' => $fromBackToWhite
                ];
            }
        }

        return $foundBlackAreas;
    }

    /**
     * Поиск областей с именем и фамилией по длине между строками.
     * @param $blackAreas
     * @param $min
     * @param $max
     * @return array
     */
    public static function searchAreasLengthBetweenLines($blackAreas, $min, $max)
    {
        $foundAreas = [];
        $numberAreas = count($blackAreas);

        for ($i = 0; $i < $numberAreas - 1; $i++) {
            $lengthBetweenAreas = $blackAreas[$i + 1]['middle'] - $blackAreas[$i]['middle'];

            if ($lengthBetweenAreas >= $min && $lengthBetweenAreas <= $max) {
                $foundAreas = [
                    $blackAreas[$i],
                    $blackAreas[$i + 1]
                ];
            }
        }

        return $foundAreas;
    }

    /**
     * Получить координаты выделяемых строк.
     * @param $foundAreas
     * @return array
     */
    public static function getCoordinatesAllocatedLines($foundAreas)
    {
        $allocatedLines = [];

        foreach ($foundAreas as $area) {
            $allocatedLines[] = $area['start'];
            $allocatedLines[] = $area['end'];
        }

        return $allocatedLines;
    }

    /**
     * Получить шаблон с интенсивностью пикселей по строкам.
     * @param $intensityPixels
     * @param $allocatedLines
     * @return string
     */
    public static function renderHorizontalIntensityPixels($intensityPixels, $allocatedLines)
    {
        $templateArray = array_map(function ($intensity, $index) use ($allocatedLines) {
            if (in_array($index, $allocatedLines)) {
                $divTemplate = "<div class='chartRow lineSelection'></div>";
            } else {
                $divTemplate = "<div class='chartRow' style='width: " . $intensity . "px'></div>";
            }

            return $divTemplate;
        }, $intensityPixels, array_keys($intensityPixels));

        $template = implode('', $templateArray);

        return $template;
    }

    /**
     * Получить шаблон с интенсивностью пикселей по столбцам.
     * @param $intensityPixels
     * @param $allocatedLines
     * @return string
     */
    public static function renderVerticalIntensityPixels($intensityPixels, $allocatedLines)
    {
        $templateArray = array_map(function ($intensity, $index) use ($allocatedLines) {
            if (in_array($index, $allocatedLines)) {
                $divTemplate = "<div class='chartCol colSelection'></div>";
            } else {
                $divTemplate = "<div class='chartCol' style='height: " . $intensity . "px'></div>";
            }

            return $divTemplate;
        }, $intensityPixels, array_keys($intensityPixels));

        $template = implode('', $templateArray);

        return $template;
    }

    /**
     * Масштабировать вырезанные символ
     *
     * @param $areaPathImage
     * @return array
     */
    public static function scalingCharacters($areaPathImage)
    {
        $result = [];

        if (!file_exists($areaPathImage)) {
            $result['error'][] = 'Изображение с выделенным символом не найдено';
            return $result;
        }

        // Информация о изображении с выделенной областью
        $areaInfoImage = Image::getInformation($areaPathImage);

        $widthCharacter = 25;
        $heightCharacter = 25;

        // Параметры для масштабирования изображения
        $paramsScaling = [
            'width' => $widthCharacter,
            'height' => $heightCharacter,
            'aspect_ratio' => true,
            'crop' => false
        ];

        // Имя масштабированного изображения
        $scalingPathImage = 'image/scaling/scaling_' . sha1('scaling' . time()) . "." . $areaInfoImage['extension'];

        // Масштабирование изображения
        $scalingCheck = Image::resize($areaPathImage, $scalingPathImage, $paramsScaling);

        if (!$scalingCheck) {
            $result['error'][] = 'Изображение не было масштабировано';

            return $result;
        }

        $result['path'] = $scalingPathImage;

        return $result;
    }

    /**
     * Вырезать символы из строки
     *
     * @param $cutLines
     * @return array
     */
    public static function cutCharacters($cutLines)
    {
        $characters = array();

        foreach ($cutLines as $line) {
            // Получить ресурс исходного изображения
            $initialImageRes = Image::getResource($line['path']);

            $infoImage = Image::getInformation($line['path']);

            foreach ($line['areas'] as $areaCharacter) {
                // Получить ширину символа
                $widthCharacter = $areaCharacter['end'] - $areaCharacter['start'];

                // Создать ресурс изображения для вырезаемого символа
                $characterRes = imagecreatetruecolor($widthCharacter, $infoImage['height']);

                // Вырезаем область символа
                imagecopy($characterRes, $initialImageRes, 0, 0, $areaCharacter['start'], 0, $widthCharacter, $infoImage['height']);

                // Генерируем уникальное для вырезанного символа
                $uniqueName = sha1('character' . microtime()) . "." . $infoImage['extension'];
                $pathImage = 'image/' . $uniqueName;

                // Сохранение символа в новом изображении
                imagejpeg($characterRes, $pathImage, 100);

                // Получаем матрицу пикселей интенсивности изображения
                $matrix = Image::getMatrix($pathImage, 'binary');

                $extremePoints = Image::searchExtremePoints($matrix);

                // Вырезаем часть изображения по крайним точкам
                $areaPathImage = Image::getSignificantArea($pathImage, $extremePoints);

                // Масштабируем изображение
                $scalingImage = Image::scalingCharacters($areaPathImage);

                // Получаем матрицу пикселей интенсивности масштабированного изображения
                $matrix = Image::getMatrix($scalingImage['path'], 'binary');

                $characters[] = array(
                    'path' => $scalingImage['path'],
                    'matrix' => $matrix,
                );

                sleep(1);
            }
        }

        return $characters;
    }
}
