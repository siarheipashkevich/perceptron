<?php namespace App\Http\Controllers;

use App\Services\Probe;
use App\Services\Image;
use App\Services\Thinning;

class HomeController extends Controller
{
	public function index()
	{
		return view('angular', array('title' => 'AngularJS + Laravel 5'));
	}

	/**
	 * Загрузка изображения и поиск зондов
	 */
	public function uploadProbes()
	{
		// Массив с данными для отправки ответа
		$response = array();

		// Загружаем изображение
		$pathImage = Image::upload($_FILES['file']);

		if ($pathImage) {
			// Получаем матрицу пикселей интенсивности изображения
			$matrix = Image::getMatrix($pathImage, 'binary');

			if (!empty($matrix)) {
				// Получаем информации о изображении
				$infoImage = Image::getInformation($pathImage);

				// Поиск зондов
				$detectedProbes = Probe::searchProbes($matrix);

				// Формируем данные для ответа
				$response = [
					'path' => $pathImage,
					'width' => $infoImage['width'],
					'height' => $infoImage['height'],
					'probes' => $detectedProbes
				];
			} else {
				$response['error'] = 'Матрица интенсивности пикселей пуста';
			}
		} else {
			$response['error'] = 'Изображение не было загружено';
		}

		return $response;
	}

	public function uploadImage()
	{
		// Массив с данными для отправки ответа
		$response = [
			'data' => [],
			'error' => []
		];

		// Загружаем изображение
		$pathOriginImage = Image::upload($_FILES['file']);

		if ($pathOriginImage) {
			// Получаем информации о исходном изображении
			$infoOriginImage = Image::getInformation($pathOriginImage);
			$response['data']['origin'] = [
				'path' => $pathOriginImage,
				'width' => $infoOriginImage['width'],
				'height' => $infoOriginImage['height']
			];

			// Получаем матрицу пикселей интенсивности изображения
			$matrix = Image::getMatrix($pathOriginImage, 'binary');

			if (!empty($matrix)) {
				$extremePoints = Image::searchExtremePoints($matrix);

				// Вырезаем часть изображения по крайним точкам
				$areaPathImage = Image::getSignificantArea($pathOriginImage, $extremePoints);

				$probesRequest = json_decode($_REQUEST['probe'], true);

				// Масштабируем изображение
				$scalingImage = Image::scaling($areaPathImage, $probesRequest['path']);

				if (!isset($scalingImage['error'])) {
					$scalingPathImage = $scalingImage['path'];

					// Получаем информации о масштабированном изображении
					$scalingInfoImage = Image::getInformation($scalingPathImage);

					// Бинаризируем изображение
					$binarizationMatrix = Image::thresholdBinarization($scalingPathImage);

					// Убрать
					$binarizationPathImage = 'image/binary_' . sha1('binary' . time())
						                   . '.' . $scalingInfoImage['extension'];
					Image::createImageByMatrix($binarizationMatrix, $binarizationPathImage);

					// Утоньшаем изображение
					$thinning = new Thinning($binarizationMatrix);
					$thinningMatrix = $thinning->ZhangSuen();
					$thinningPathImage = 'image/thinning_' . sha1('thinning' . time())
						               . '.' . $scalingInfoImage['extension'];
					Image::createImageByMatrix($thinningMatrix, $thinningPathImage);

					// Получаем информации о результируещем изображении
					$thinningInfoImage = Image::getInformation($thinningPathImage);

					// Получаем количество пересечений с каждым зондом
					$intersections = Probe::defineIntersection($probesRequest['probes'], $thinningMatrix);
					$response['data']['intersections'] = $intersections;

					$response['data']['processed'] = [
						'path' => $thinningPathImage,
						'width' => $thinningInfoImage['width'],
						'height' => $thinningInfoImage['height']
					];
				} else {
					$response['error'][] = $scalingImage['error'];
				}
			} else {
				$response['error'][] = 'Матрица интенсивности пикселей пуста';
			}
		} else {
			$response['error'][] = 'Изображение не было загружено';
		}

		return $response;
	}
}
