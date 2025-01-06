<?php

namespace Api\Utils;

class Qibla
{
    public static function kaabaX1(float $degrees, int $needleX, int $kaabaW,  int $kaabaH) : int
    {
        if ($degrees < 45) {
            return round($needleX - $kaabaW / 2);
        } else if ($degrees >= 45 && $degrees < 90) {
            return round($needleX);
        } else if ($degrees >= 90 && $degrees < 135) {
            return round($needleX);
        } else if ($degrees >= 135 && $degrees < 180) {
            return round($needleX - $kaabaH / 4);
        } else if ($degrees >= 180 && $degrees < 225) {
            return round($needleX - $kaabaW / 2);
        } else if ($degrees >= 225 && $degrees < 270) {
            return round($needleX - $kaabaW);
        } else if ($degrees >= 270 && $degrees < 315) {
            return round($needleX - $kaabaW);
        }else if ($degrees >= 315 && $degrees < 360) {
            return round($needleX - $kaabaW / 2);
        }

        return $needleX;
    }

    public static function kaabaY1(float $degrees, int $needleY, int $kaabaW,  int $kaabaH) : int
    {
        if ($degrees < 45) {
            return $needleY - $kaabaH;
        } else if ($degrees >= 45 && $degrees < 90) {
            return round($needleY - $kaabaH / 2);
        } else if ($degrees >= 90 && $degrees < 135) {
            return round($needleY - $kaabaH / 2);
        } else if ($degrees >= 135 && $degrees < 180) {
            return round($needleY);
        }else if ($degrees >= 180 && $degrees < 225) {
            return round($needleY);
        } else if ($degrees >= 225 && $degrees < 270) {
            return round($needleY - $kaabaH / 2);
        } else if ($degrees >= 270 && $degrees < 315) {
            return round($needleY - $kaabaH / 2);
        } else if ($degrees >= 315 && $degrees < 360) {
            return round($needleY - $kaabaH);
        }

        return $needleY;
    }
}