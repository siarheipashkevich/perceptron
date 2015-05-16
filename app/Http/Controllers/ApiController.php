<?php namespace App\Http\Controllers;

use App\Services\Image;
use App\Data;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    const LENGTH_BETWEEN_STRING_MIN = 15;
    const LENGTH_BETWEEN_STRING_MAX = 25;

    public function upload()
    {
        $response = [];

        $pathImage = Image::upload($_FILES['file']);

        $type = \Request::input('type');
        if (!isset($type)) {
            $type = 'binary';
        }

        $matrix = Image::getMatrix($pathImage, $type);

        $response['path'] = $pathImage;
        $response['matrix'] = $matrix;

        return $response;
    }

    public function convertGrayscale()
    {
        $image = \Request::get('image');

        $matrix = Image::convertRgbToGrayscale($image['matrix']);
        $infoImage = Image::getInformation($image['path']);

        $newDataImage = [
            'path'      => Image::createGrayscaleByMatrix($matrix, $infoImage['extension']),
            'matrix'    => $matrix
        ];

        return $newDataImage;
    }

    public function convertBinary()
    {
        $image = \Request::get('image');
        $threshold = \Request::get('threshold');
        $infoImage = Image::getInformation($image['path']);

        $binaryMatrix = Image::thresholdBinarization($image['path'], $threshold, $image['matrix']);

        $newDataImage = [
            'path'      => Image::createImageByMatrix1($binaryMatrix, $infoImage['extension']),
            'matrix'    => $binaryMatrix
        ];

        return $newDataImage;
    }

    public function saveData()
    {
        $dataInput = \Request::input('input');
        $dataHidden = \Request::input('hidden');
        $dataOutput = \Request::input('output');
        $dataWeights = \Request::input('weights');
        $dataWeightsSum = \Request::input('weightsSum');
        $dataValuesY = \Request::input('valuesY');
        $dataImages = \Request::input('images');
        $dataCompounds = \Request::input('compounds');
        $dataTitle = \Request::input('title');

        $data = new Data();
        $data->input = $dataInput;
        $data->hidden = $dataHidden;
        $data->output = $dataOutput;
        $data->weights = json_encode($dataWeights);
        $data->weightsSum = json_encode($dataWeightsSum);
        $data->valuesY = json_encode($dataValuesY);
        $data->images = json_encode($dataImages);
        $data->compounds = json_encode($dataCompounds);
        $data->title = $dataTitle;

        if (!$data->save()) {
            abort(500, "Saving failed.");
        }

        return $data;
    }

    public function getData()
    {
        $data = Data::all();

        return $data;
    }

    public function horizontalChart()
    {
        $matrix = \Request::get('matrix');

        $intensityPixels = Image::getIntensityPixelsLines($matrix, 20);

        $blackAreas = Image::searchBlackAreas($intensityPixels);

        $foundAreas = Image::searchAreasLengthBetweenLines(
            $blackAreas,
            self::LENGTH_BETWEEN_STRING_MIN,
            self::LENGTH_BETWEEN_STRING_MAX
        );

        $allocatedLines = Image::getCoordinatesAllocatedLines($foundAreas);

        $template = Image::renderHorizontalIntensityPixels($intensityPixels, $allocatedLines);

        $response = [
            'template' => $template,
            'foundAreas' => $foundAreas
        ];

        return $response;
    }

    public function cutLines()
    {
        $foundAreas = \Request::get('foundAreas');
        $imagePath = \Request::get('imagePath');

        $infoImage = Image::getInformation($imagePath);

        // Получить ресурс исходного изображения
        $initialImageRes = Image::getResource($imagePath);

        // Получить высоты двух вырезаемых строк
        $oneHeightLine = $foundAreas[0]['end'] - $foundAreas[0]['start'];
        $twoHeightLine = $foundAreas[1]['end'] - $foundAreas[1]['start'];

        // Получить ресурсы изображений для вырезаемых строк
        $oneLineRes = imagecreatetruecolor($infoImage['width'], $oneHeightLine);
        $twoLineRes = imagecreatetruecolor($infoImage['width'], $twoHeightLine);

        // Вырезаем области иозбражения
        imagecopy($oneLineRes, $initialImageRes, 0, 0, 0, $foundAreas[0]['start'], $infoImage['width'], $oneHeightLine);
        imagecopy($twoLineRes, $initialImageRes, 0, 0, 0, $foundAreas[1]['start'], $infoImage['width'], $twoHeightLine);

        // Генерируем уникальные имена для двух вырезанных строк
        $oneUniqueName = sha1('oneLine' . microtime()) . "." . $infoImage['extension'];
        $onePathImage = 'image/' . $oneUniqueName;
        $twoUniqueName = sha1('twoLine' . microtime()) . "." . $infoImage['extension'];
        $twoPathImage = 'image/' . $twoUniqueName;

        // Вывод изображений в новые файлы
        imagejpeg($oneLineRes, $onePathImage, 100);
        imagejpeg($twoLineRes, $twoPathImage, 100);

        // Получаем матрицы бинарных изображений
        $oneBinaryMatrix = Image::getMatrix($onePathImage, 'binary');
        $twoBinaryMatrix = Image::getMatrix($twoPathImage, 'binary');

        // Получаем интенсивности пикселей для двух матриц по столбцам
        $oneIntensityPixels = Image::getIntensityPixelsColumns($oneBinaryMatrix);
        $twoIntensityPixels = Image::getIntensityPixelsColumns($twoBinaryMatrix);

        // Поиск чёрных областей по интенсивности
        $oneBlackAreas = Image::searchBlackAreas($oneIntensityPixels);
        $twoBlackAreas = Image::searchBlackAreas($twoIntensityPixels);

        // Выделение найденных областей
        $oneAllocatedColumns = Image::getCoordinatesAllocatedLines($oneBlackAreas);
        $twoAllocatedColumns = Image::getCoordinatesAllocatedLines($twoBlackAreas);

        // Получить шаблоны с интенсивностью пикселей по столбцам
        $oneTemplate = Image::renderVerticalIntensityPixels($oneIntensityPixels, $oneAllocatedColumns);
        $twoTemplate = Image::renderVerticalIntensityPixels($twoIntensityPixels, $twoAllocatedColumns);

        $response = array(
            array (
                'path' => $onePathImage,
                'matrix' => $oneBinaryMatrix,
                'template' => $oneTemplate,
                'areas' => $oneBlackAreas
            ),
            array (
                'path' => $twoPathImage,
                'matrix' => $twoBinaryMatrix,
                'template' => $twoTemplate,
                'areas' => $twoBlackAreas
            )
        );

        return $response;
    }

    public function cutCharacters()
    {
        $cutLines = \Request::get('cutLines');

        $characters = Image::cutCharacters($cutLines);

        return $characters;
    }
}