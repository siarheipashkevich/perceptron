<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>{{ $title  }}</title>
	<link href="/css/app.css" rel="stylesheet">
</head>
<body ng-cloak>
	<nav class="navbar navbar-default">
		<div class="container-fluid">
			<div class="navbar-header">
				<a class="navbar-brand" href="#">Распознование изображений</a>
			</div>
		</div>
	</nav>

	@yield('content')

    <script src="/js/dist/lib.js"></script>

	<script src="/js/app/core/core.module.js"></script>
	<script src="/js/app/core/core.config.js"></script>

	<script src="/js/app/shared/shared.module.js"></script>
	<script src="/js/app/shared/shared.lodash.factory.js"></script>

	<script src="/js/app/recognition/recognition.module.js"></script>
	<script src="/js/app/recognition/recognition.config.js"></script>
	<script src="/js/app/recognition/recognition.controller.js"></script>
	<script src="/js/app/recognition/modal.controller.js"></script>
	<script src="/js/app/recognition/modal/compounds.controller.js"></script>
	<script src="/js/app/recognition/modal/weightsSum.controller.js"></script>
	<script src="/js/app/recognition/modal/weights.controller.js"></script>
	<script src="/js/app/recognition/modal/valuesY.controller.js"></script>
	<script src="/js/app/recognition/modal/main.controller.js"></script>
	<script src="/js/app/recognition/recognition.service.js"></script>
	<script src="/js/app/recognition/image.service.js"></script>
	<script src="/js/app/recognition/Perceptron.js"></script>
	<script src="/js/app/app.module.js"></script>
</body>
</html>
