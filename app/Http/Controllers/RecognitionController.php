<?php namespace App\Http\Controllers;

use App\Services\Image;

class RecognitionController extends Controller
{
    private $pathImage = 'image/';

    private function createDirIfNotExists($name)
    {
        $dir = $this->pathImage . $name;

        if (!is_dir('public/' . $dir)) {
            mkdir('public/' . $dir, 0755, true);
        }
    }

    public function upload()
    {
        $response = [];

        $pathImage = Image::upload($_FILES['file']);
        $matrix = Image::getMatrix($pathImage, 'color');

        $response['path'] = $pathImage;
        $response['matrix'] = $matrix;

        return $response;
    }

    public function convert()
    {
        $response = [];

        $images = \Request::get('images');

        foreach ($images as $image) {
            $matrix = Image::convertRgbToGrayscale($image['matrix']);
            $infoImage = Image::getInformation($image['path']);

            $newDataImage = [
                'path'      => Image::createGrayscaleByMatrix($matrix, $infoImage['extension']),
                'matrix'    => $matrix
            ];

            if (isset($image['typeClass'])) {
                $newDataImage['typeClass'] = $image['typeClass'];
            }

            $response[] = $newDataImage;
        }

        return $response;
    }

    public function recognition()
    {
        $initialMatrixImages = \Request::get('initialMatrixImages');
        $recognitionMatrixImage = \Request::get('recognitionMatrixImage');

        $response = Image::distanceEuclid($initialMatrixImages, $recognitionMatrixImage);

        return $response;
    }

    public function binary()
    {
        $images = \Request::get('images');
        $threshold = \Request::get('threshold');

        $response = Image::convertGrayscaleToBinary($images, $threshold);

        return $response;
    }
}