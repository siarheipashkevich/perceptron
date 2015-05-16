<?php namespace App\Services;

class Thinning
{
    private $bitmap;

    public function __construct(array $bitmap)
    {
        $this->bitmap = $bitmap;
    }

    public function ZhangSuen()
    {
        $p = array();

        $flag = true;

        $w = count($this->bitmap);
        $h = count($this->bitmap[0]);

        $markMap = array();
        for ($i = 0; $i < $w; $i++) {
            for ($j = 0; $j < $h; $j++) {
                $markMap[$i][$j] = 0;
            }
        }

        while ($flag) {
            $flag = false;

            /*  Подытерация 1: */
            for ($i = 1; $i < $w - 1; $i++) {
                for ($j = 1; $j < $h - 1; $j++) {
                    if ($this->bitmap[$i][$j] == 0) {
                        $markMap[$i][$j] = 0;
                        continue;
                    }

                    $a = $this->Tla($this->bitmap, $i, $j, $p, $b);
                    $p1 = $p[0] * $p[2] * $p[4];
                    $p2 = $p[2] * $p[4] * $p[6];

                    if (($a == 1) && (($b >= 2) && ($b <= 6)) && ($p1 == 0) && ($p2 == 0)) {
                        $markMap[$i][$j] = 1;
                        $flag = true;
                    } else {
                        $markMap[$i][$j] = 0;
                    }
                }
            }
            $this->deleteMarked($markMap, $this->bitmap);

            /* Подытерация 2: */
            for ($i = 1; $i < $w - 1; $i++) {
                for ($j = 1; $j < $h - 1; $j++) {
                    if ($this->bitmap[$i][$j] == 0) {
                        $markMap[$i][$j] = 0;
                        continue;
                    }

                    $a = $this->Tla($this->bitmap, $i, $j, $p, $b);
                    $p1 = $p[0] * $p[2] * $p[6];
                    $p2 = $p[0] * $p[4] * $p[6];

                    if (($a == 1) && (($b >= 2) && ($b <= 6)) && ($p1 == 0) && ($p2 == 0)) {
                        $markMap[$i][$j] = 1;
                        $flag = true;
                    } else {
                        $markMap[$i][$j] = 0;
                    }
                }
            }
            $this->deleteMarked($markMap, $this->bitmap);
        }

        /* Доролнительая итерация, устраняющая некоторые недочеты */
        for ($i = 1; $i < $w - 1; $i++) {
            for ($j = 1; $j < $h - 1; $j++) {
                if ($this->bitmap[$i][$j] == 0) {
                    $markMap[$i][$j] = 0;
                    continue;
                }

                $this->Tla($this->bitmap, $i, $j, $p, $b);
                $p1 = (($p[7] == 1) ? 0 : 1) * $p[2] * $p[4];
                $p2 = (($p[3] == 1) ? 0 : 1) * $p[6] * $p[0];
                $p3 = (($p[1] == 1) ? 0 : 1) * $p[4] * $p[6];
                $p4 = (($p[5] == 1) ? 0 : 1) * $p[0] * $p[2];

                if ($p1 == 1 || $p2 == 1 || $p3 == 1 || $p4 == 1) {
                    $this->bitmap[$i][$j] = 0;
                }
            }
        }

        return $this->bitmap;
    }

    private function Tla($image, $i, $j, &$p, &$b)
    {
        $m = 0;
        $n = 0;

        $p[0] = $image[$i - 1][$j];
        $p[1] = $image[$i - 1][$j + 1];
        $p[2] = $image[$i][$j + 1];
        $p[3] = $image[$i + 1][$j + 1];
        $p[4] = $image[$i + 1][$j];
        $p[5] = $image[$i + 1][$j - 1];
        $p[6] = $image[$i][$j - 1];
        $p[7] = $image[$i - 1][$j - 1];

        for ($k = 0; $k < 7; $k++) {
            if (($p[$k] == 0) && ($p[$k + 1] == 1)) {
                $m++;
            }

            $n += $p[$k];
        }

        if (($p[7] == 0) && ($p[0] == 1)) {
            $m++;
        }

        $n += $p[7];
        $b = $n;

        return $m;
    }

    private function deleteMarked($markBmp, &$bitMap)
    {
        $w = count($bitMap);
        $h = count($bitMap[0]);

        for ($i = 0; $i < $w; $i++) {
            for ($j = 0; $j < $h; $j++) {
                $bitMap[$i][$j] -= $markBmp[$i][$j];
            }
        }
    }
}
