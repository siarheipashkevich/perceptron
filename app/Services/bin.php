/**
* Глобальная бинаризация изображения (пороговая)
*
* @param $pathImage
* @return array
*/
public static function binarization($pathImage)
{
$binarization = array();

$matrix = self::getMatrix($pathImage, 'grayscale');

$width = count($matrix);
$height = count($matrix[0]);

//        for ($i = 0; $i < $width; $i++) {
//            for ($j = 0; $j < $height; $j++) {
//                if ($matrix[$i][$j] < self::THRESHOLD) {
//                    $binarization[$i][$j] = 1;
//                } else {
//                    $binarization[$i][$j] = 0;
//                }
//            }
//        }

$AM = $matrix;

$COPY = array();

$leftTop = $leftBottom = $topLeft = $topRight = $rightTop = $rightBottom = $bottomLeft = $bottomRight = array();

for ($i = 0; $i < $width; $i++) {
for ($j = 0; $j < $height; $j++) {
$maxLeft = $j;
$maxTop = $i;

// left
$a1 = $j - 1;
// top
$a2 = $i - 1;
// right
$a3 = $j + 1;
// bottom
$a4 = $i + 1;

$left = $top = $right = $bottom = false;

if ($a1 < $maxLeft && $a1 >= 0) {
$left = true;
}
if ($a2 < $maxTop && $a2 >= 0) {
$top = true;
}
if ($a3 < $height) {
$right = true;
}
if ($a4 < $width) {
$bottom = true;
}

if ($left && $bottom) {
// Left and bottom
$fixColumn = $a1;
$endLine = $a4;
if (!$top) {
$startLine = $i;
} else {
$startLine = $i + 1;
}

for ($k = $startLine; $k <= $endLine; $k++) {
$tempArray['value'] = $AM[$k][$fixColumn];
$tempArray['x'] = $a1 - $j;
$tempArray['y'] = $k - $i;

array_push($leftBottom, $tempArray);
}
unset($tempArray);
// End left and bottom

// Bottom and left
$fixLine = $a4;
$endColumn = $a1;
$startColumn = $j;

for ($k = $startColumn; $k > $endColumn; $k--) {
$tempArray['value'] = $AM[$fixLine][$k];
$tempArray['x'] = $j - $k;
$tempArray['y'] = $i - $a4;

array_push($bottomLeft, $tempArray);
}
unset($tempArray);
// End Bottom and Left
}

if ($right && $top) {
// Right and top
$fixColumn = $a3;
$endLine = $a2;
$startLine = $i;

for ($k = $startLine; $k >= $endLine; $k--) {
$tempArray['value'] = $AM[$k][$fixColumn];
$tempArray['x'] = $a3 - $j;
$tempArray['y'] = $i - $k;

array_push($rightTop, $tempArray);
}
unset($tempArray);
// End Right and Top

// Top and Right
$fixLine = $a2;
$endColumn = $a3;
if (!$left) {
$startColumn = $j;
} else {
$startColumn = $j + 1;
}

for ($k = $startColumn; $k < $endColumn; $k++) {
$tempArray['value'] = $AM[$fixLine][$k];
$tempArray['x'] = $k - $j;
$tempArray['y'] = $a2 - $i;

array_push($topRight, $tempArray);
}
unset($tempArray);
// End Top and Right
}

if ($bottom && $right) {
// Right and bottom
$fixColumn = $a3;
$endLine = $a4;

if (!$top && $right) {
$startLine = $i;
} else {
$startLine = $i + 1;
}

for ($k = $startLine; $k <= $endLine; $k++) {
$tempArray['value'] = $AM[$k][$fixColumn];
$tempArray['x'] = $a3 - $j;
$tempArray['y'] = $k - $i;

array_push($rightBottom, $tempArray);
}
unset($tempArray);
// End right and top

// Bottom and right
$fixLine = $a4;
$endColumn = $a3;
if (!$left) {
$startColumn = $j;
} else {
$startColumn = $j + 1;
}

for ($k = $startColumn; $k < $endColumn; $k++) {
$tempArray['value'] = $AM[$fixLine][$k];
$tempArray['x'] = $k - $j;
$tempArray['y'] = $a4 - $i;

array_push($bottomRight, $tempArray);
}
unset($tempArray);
// End bottom and right
}

if ($left && $top) {
// Left and Top
$fixColumn = $a1;
$endLine = $a2;
$startLine = $i;

for ($k = $startLine; $k >= $endLine; $k--) {
$tempArray['value'] = $AM[$k][$fixColumn];
$tempArray['x'] = $j - $a1;
$tempArray['y'] = $i - $k;

array_push($leftTop, $tempArray);
}
unset($tempArray);
// End left and top

// Top and Left
$fixLine = $a2;
$endColumn = $a1;
$startColumn = $j;

for ($k = $startColumn; $k > $endColumn; $k--) {
$tempArray['value'] = $AM[$fixLine][$k];
$tempArray['x'] = $j - $k;
$tempArray['y'] = $i - $a2;

array_push($topLeft, $tempArray);
}
unset($tempArray);
// End top and left
}

//$m1=$m2=$m3=$m4=$m6=$m7=$m8=$m9=0;

// 6 element, 9 element, 8 element
if ($bottom && $right) {
// 6 element, 9 element
if (!$top && $right) {
$m6 = $rightBottom[0]['value'];
$m9 = $rightBottom[1]['value'];
} else {
$m9 = $rightBottom[0]['value'];
}

// 8 element
if (!$left) {
$m8 = $bottomRight[0]['value'];
}
}

// 4 element, 7 element, 8 element
if ($left && $bottom) {
// 4 element, 7 element
if (!$top) {
$m4 = $leftBottom[0]['value'];
$m7 = $leftBottom[1]['value'];
} else {
$m7 = $leftBottom[0]['value'];
}

// 8 element ! берётся всегда, если есть доступ влево
$m8 = $bottomLeft[0]['value'];
}

// 1 element, 2 element, 4 element
if ($left && $top) {
$m1 = $leftTop[1]['value'];
$m2 = $topLeft[0]['value'];
$m4 = $leftTop[0]['value'];
}

if ($right && $top) {
$m6 = $rightTop[0]['value'];
$m3 = $rightTop[1]['value'];

if (!$left) {
$m2 = $topRight[0]['value'];
}
}

$m1 = (isset($m1)) ? $m1 : 255;
$m2 = (isset($m2)) ? $m2 : 255;
$m3 = (isset($m3)) ? $m3 : 255;
$m4 = (isset($m4)) ? $m4 : 255;
$m5 = $AM[$i][$j];
$m6 = (isset($m6)) ? $m6 : 255;
$m7 = (isset($m7)) ? $m7 : 255;
$m8 = (isset($m8)) ? $m8 : 255;
$m9 = (isset($m9)) ? $m9 : 255;

$result = array(
$m1,
$m2,
$m3,
$m4,
$m5,
$m6,
$m7,
$m8,
$m9,
);
$bottomLeft = $leftBottom = $topRight = $rightTop = $bottomRight = $rightBottom = $topLeft = $leftTop = array();

$sumMaskAndElement = 0;
for ($t = 0; $t < 9; $t++) {
$sumMaskAndElement += $result[$t];
}

$sumMaskAndElement = $sumMaskAndElement / 9;

$COPY[$i][$j] = round($sumMaskAndElement);
}
}

for ($i = 0; $i < $width; $i++) {
for ($j = 0; $j < $height; $j++) {
if ($AM[$i][$j] < $COPY[$i][$j]) {
$binarization[$i][$j] = 1;
} else {
$binarization[$i][$j] = 0;
}
}
}

return $binarization;
}