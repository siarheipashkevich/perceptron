@extends('app')

@section('content')
<div class="container-fluid">
	<div ng-controller="AppController" style="display: none" class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="col-md-12">
					<table class="table table-hover table-bordered">
						<thead>
						<tr>
							<th>Номер изображения</th>
							<td ng-repeat="headProbe in probe.probes">
								<% $index + 1 %>
							</td>
							<th>Тип класса</th>
							<th>Символ</th>
						</tr>
						</thead>
						<tbody>
						<tr ng-repeat="result in results">
							<td><% result.name %></td>
							<td ng-repeat="intersection in result.probes track by $index">
								<% intersection %>
							</td>
							<td><% result.class %></td>
							<td><% result.letter %></td>
						</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="row">
				<div class="col-md-3">
					<div class="panel panel-default">
						<div class="panel-body">
							<div class="row">
								<div class="col-md-12">
									<div ng-file-select
										 ng-file-change="uploadProbes($files, $event)"
										 ng-accept="'image/*'"
										 class="btn btn-primary btn-sm btn-block">
										Загрузить маску с зондами
									</div>
								</div>
								<div class="col-md-12" ng-show="probe">
									<h6><strong>Изображение с зондами:</strong></h6>
									<div class="row">
										<div class="col-md-3">
											<img ng-src="<% probe.path %>" style="border: 1px solid #DDD;">
										</div>
										<div class="col-md-9">
											<ul class="list-unstyled information-image">
												<li>
													<div class="clearfix">
														<div class="pull-left"><small>Ширина:</small></div>
														<div class="pull-right"><small><% probe.width %> px</small></div>
													</div>
												</li>
												<li>
													<div class="clearfix">
														<div class="pull-left"><small>Высота:</small></div>
														<div class="pull-right"><small><% probe.height %> px</small></div>
													</div>
												</li>
												<li>
													<div class="clearfix">
														<div class="pull-left"><small>Количество зондов:</small></div>
														<div class="pull-right"><small><% probe.probes.length %></small></div>
													</div>
												</li>
											</ul>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-3">
					<div class="panel panel-default">
						<div class="panel-body">
							<div class="row">
								<div class="col-md-12">
									<div ng-file-select
										 ng-file-change="uploadImage($files, $event, 'one')"
										 ng-accept="'image/*'"
										 ng-disabled="!probe"
										 class="btn btn-primary btn-sm btn-block">
										Выберите первое изображение
									</div>
								</div>
								<div class="col-md-12" ng-show="images.one.origin">
									<h6><strong>Исходное изображение:</strong></h6>
									<div class="row">
										<div class="col-md-3">
											<img ng-src="<% images.one.origin.path %>" style="border: 1px solid #DDD;">
										</div>
										<div class="col-md-9">
											<ul class="list-unstyled information-image">
												<li>
													<div class="clearfix">
														<div class="pull-left">
															<small>Ширина:</small>
														</div>
														<div class="pull-right">
															<small><% images.one.origin.width %> px</small>
														</div>
													</div>
												</li>
												<li>
													<div class="clearfix">
														<div class="pull-left">
															<small>Высота:</small>
														</div>
														<div class="pull-right">
															<small><% images.one.origin.height %> px</small>
														</div>
													</div>
												</li>
											</ul>
										</div>
									</div>
								</div>
								<div class="col-md-12" ng-show="images.one.processed">
									<h6><strong>Обработанное изображение:</strong></h6>
									<div class="row">
										<div class="col-md-3">
											<img ng-src="<% images.one.processed.path %>" style="border: 1px solid #DDD;">
										</div>
										<div class="col-md-9">
											<ul class="list-unstyled information-image">
												<li>
													<div class="clearfix">
														<div class="pull-left"><small>Ширина области:</small></div>
														<div class="pull-right">
															<small><% images.one.processed.width %> px</small>
														</div>
													</div>
												</li>
												<li>
													<div class="clearfix">
														<div class="pull-left"><small>Высота области:</small></div>
														<div class="pull-right">
															<small><% images.one.processed.height %> px</small>
														</div>
													</div>
												</li>
											</ul>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-3">
					<div class="panel panel-default">
						<div class="panel-body">
							<div class="row">
								<div class="col-md-12">
									<div ng-file-select
										 ng-file-change="uploadImage($files, $event, 'two')"
										 ng-accept="'image/*'"
										 ng-disabled="!probe"
										 class="btn btn-primary btn-sm btn-block">
										Выберите второе изображение
									</div>
								</div>
								<div class="col-md-12" ng-show="images.two.origin">
									<h6><strong>Исходное изображение:</strong></h6>
									<div class="row">
										<div class="col-md-3">
											<img ng-src="<% images.two.origin.path %>" style="border: 1px solid #DDD;">
										</div>
										<div class="col-md-9">
											<ul class="list-unstyled information-image">
												<li>
													<div class="clearfix">
														<div class="pull-left">
															<small>Ширина:</small>
														</div>
														<div class="pull-right">
															<small><% images.two.origin.width %> px</small>
														</div>
													</div>
												</li>
												<li>
													<div class="clearfix">
														<div class="pull-left">
															<small>Высота:</small>
														</div>
														<div class="pull-right">
															<small><% images.two.origin.height %> px</small>
														</div>
													</div>
												</li>
											</ul>
										</div>
									</div>
								</div>
								<div class="col-md-12" ng-show="images.two.processed">
									<h6><strong>Обработанное изображение:</strong></h6>
									<div class="row">
										<div class="col-md-3">
											<img ng-src="<% images.two.processed.path %>" style="border: 1px solid #DDD;">
										</div>
										<div class="col-md-9">
											<ul class="list-unstyled information-image">
												<li>
													<div class="clearfix">
														<div class="pull-left"><small>Ширина области:</small></div>
														<div class="pull-right">
															<small><% images.two.processed.width %> px</small>
														</div>
													</div>
												</li>
												<li>
													<div class="clearfix">
														<div class="pull-left"><small>Высота области:</small></div>
														<div class="pull-right">
															<small><% images.two.processed.height %> px</small>
														</div>
													</div>
												</li>
											</ul>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-3">
					<div class="panel panel-default">
						<div class="panel-body">
							<div class="row">
								<div class="col-md-12">
									<div ng-file-select
										 ng-file-change="uploadImage($files, $event, 'three')"
										 ng-accept="'image/*'"
										 ng-disabled="!probe"
										 class="btn btn-primary btn-sm btn-block">
										Выберите третье изображение
									</div>
								</div>
								<div class="col-md-12" ng-show="images.three.origin">
									<h6><strong>Исходное изображение:</strong></h6>
									<div class="row">
										<div class="col-md-3">
											<img ng-src="<% images.three.origin.path %>" style="border: 1px solid #DDD;">
										</div>
										<div class="col-md-9">
											<ul class="list-unstyled information-image">
												<li>
													<div class="clearfix">
														<div class="pull-left">
															<small>Ширина:</small>
														</div>
														<div class="pull-right">
															<small><% images.three.origin.width %> px</small>
														</div>
													</div>
												</li>
												<li>
													<div class="clearfix">
														<div class="pull-left">
															<small>Высота:</small>
														</div>
														<div class="pull-right">
															<small><% images.three.origin.height %> px</small>
														</div>
													</div>
												</li>
											</ul>
										</div>
									</div>
								</div>
								<div class="col-md-12" ng-show="images.three.processed">
									<h6><strong>Масштабированное изображение:</strong></h6>
									<div class="row">
										<div class="col-md-3">
											<img ng-src="<% images.three.processed.path %>" style="border: 1px solid #DDD;">
										</div>
										<div class="col-md-9">
											<ul class="list-unstyled information-image">
												<li>
													<div class="clearfix">
														<div class="pull-left"><small>Ширина области:</small></div>
														<div class="pull-right">
															<small><% images.three.processed.width %> px</small>
														</div>
													</div>
												</li>
												<li>
													<div class="clearfix">
														<div class="pull-left"><small>Высота области:</small></div>
														<div class="pull-right">
															<small><% images.three.processed.height %> px</small>
														</div>
													</div>
												</li>
											</ul>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div ng-controller="RecController" class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="col-md-12">
					<div class="row">
						<div class="col-md-3" ng-repeat="section in sectionClasses">
							<div class="panel panel-default">
								<div class="panel-body">
									<div class="row">
										<div class="col-md-12">
											<div ng-file-select
												 ng-multiple="true"
												 ng-file-change="uploadImages($files, section.typeClass)"
												 ng-accept="'image/*'"
												 class="btn btn-success btn-sm btn-block">
												<% section.btn %>
											</div>
										</div>
										<div class="col-md-12 divImages" ng-show="classes[section.typeClass].color.length">
											<ul class="list-unstyled listImages">
												<li ng-repeat="image in classes[section.typeClass].color">
													<img ng-src="<% image.path %>" class="img-thumbnail" title="<% image.path %>">
												</li>
											</ul>
										</div>
										<div style="margin-top: 10px" class="col-md-12" ng-show="classes[section.typeClass].color.length">
											<button ng-click="convertColorToGrayscale(section.typeClass)" class="btn btn-sm btn-block btn-primary">
												Перевести в полутоновое
											</button>
										</div>
										<div class="col-md-12" ng-show="classes[section.typeClass].grayscale.length">
											<ul class="list-unstyled listImages">
												<li ng-repeat="image in classes[section.typeClass].grayscale">
													<img ng-src="<% image.path %>" class="img-thumbnail">
												</li>
											</ul>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="col-md-12">
					<div class="panel panel-default">
						<div class="panel-body">
							<div class="clearfix">
								<div class="pull-left">
									<div ng-file-select
										 ng-disabled="isEmpty(classes)"
										 ng-file-change="uploadImageForRecognition($files)"
										 ng-accept="'image/*'"
										 class="btn btn-sm btn-default"
										 ng-click="">
										Загрузить изображение для распознавания
									</div>
									<img ng-src="<% recognitionImage.color.path %>"
										 ng-show="recognitionImage.color.path"
										 width="30px"
										 class="img-thumbnail">
									<div class="btn btn-sm btn-default"
										 ng-disabled="!recognitionImage.color.path"
										 ng-click="convertColorToGrayscaleRecognitionImage()">
										Перевести в полутоновое
									</div>
									<img ng-src="<% recognitionImage.grayscale.path %>"
										 ng-show="recognitionImage.grayscale.path"
										 width="30px"
										 class="img-thumbnail">
								</div>
								<div class="pull-right">
									<div class="pull-left" style="margin-right: 30px">
										<input ng-model="threshold" type="text" class="form-control input-sm pull-left" placeholder="Порог" style="width: 60px; margin-right: 10px;">
										<div ng-click="convertGrayscaleToBinary()" class="btn btn-warning btn-sm">Перевод в бинарное</div>
									</div>
									<div class="btn btn-sm btn-danger"
										 ng-disabled="!recognitionImage.grayscale.path"
											ng-click="nearestNeighbors()">
										Метод ближайших соседей
									</div>
									<div class="btn btn-sm btn-danger"
										 ng-disabled="!recognitionImage.grayscale.path"
										 ng-click="nearestNeighbor()">
										Метод ближайшего соседа
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="col-md-12" ng-show="tableNearestNeighbors.length">
					<div class="panel panel-default">
						<!-- Default panel contents -->
						<div class="panel-heading">Таблица результатов для метода ближайших соседей</div>

						<!-- Table -->
						<div class="table-responsive">

							<table class="table table-bordered table-condensed resultTable">
								<thead>
									<tr>
										<th width="20px">№</th>
										<th width="20px">Класс</th>
										<th width="20px">R</th>
										<th ng-repeat="image in getLengthRow() track by $index">
											<% $index + 1 %>
										</th>
									</tr>
								</thead>
								<tbody>
									<tr ng-repeat="resultNeighbors in tableNearestNeighbors" ng-class="{'info': resultNeighbors.light}">
										<td><% $index + 1 %></td>
										<td><% resultNeighbors.typeClass %></td>
										<td><% resultNeighbors.r %></td>
										<td ng-repeat="matrixValue in resultNeighbors.matrix track by $index">
											<% matrixValue %>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>

				<div class="col-md-12" ng-show="tableNearestNeighbor.length">
					<div class="panel panel-default">
						<!-- Default panel contents -->
						<div class="panel-heading">Таблица результатов для метода ближайшего соседа</div>

						<!-- Table -->
						<div class="table-responsive">

							<table class="table table-bordered table-condensed resultTable">
								<thead>
								<tr>
									<th width="20px">№</th>
									<th width="20px">Класс</th>
									<th width="20px">R</th>
									<th ng-repeat="image in getLengthRow() track by $index">
										<% $index + 1 %>
									</th>
								</tr>
								</thead>
								<tbody>
								<tr ng-repeat="resultNeighbor in tableNearestNeighbor" ng-class="{'info': resultNeighbor.light}">
									<td><% $index + 1 %></td>
									<td><% resultNeighbor.typeClass %></td>
									<td><% resultNeighbor.r %></td>
									<td ng-repeat="matrixValue in resultNeighbor.matrix track by $index">
										<% matrixValue %>
									</td>
								</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>

			</div>
		</div>
	</div>
</div>
@endsection
