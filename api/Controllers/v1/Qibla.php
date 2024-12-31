<?php

namespace Api\Controllers\v1;

use Mamluk\Kipchak\Components\Controllers\Slim;
use Mamluk\Kipchak\Components\Http;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use AlQibla\Calculation;
use Api\Utils;

class Qibla extends Slim
{

    public function get(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $latitude = floatval($request->getAttribute('latitude'));
        $longitude = floatval($request->getAttribute('longitude'));
        $calculation = [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'direction' => Calculation::get($latitude, $longitude)
        ];

        return Http\Response::json($response,
            $calculation,
            200,
            true,
            604800,
            ['public']
        );
    }

    public function getCompass(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $latitude = floatval($request->getAttribute('latitude'));
        $longitude = floatval($request->getAttribute('longitude'));
        $sizeAttr = Http\Request::getAttribute($request, 'size');
        $size =  ($sizeAttr === null || (int) $sizeAttr < 1 || (int) $sizeAttr > 4000) ? 1000 : (int) $sizeAttr;
        $degrees = Calculation::get($latitude, $longitude);

        // TODO: Not great code, but it works.
        $width = 4000;
        $height = 4000;
        $angle = deg2rad($degrees - 90); // Adjust for compass orientation
        $needleLength = 1500;
        $needleX = floor($width / 2 + $needleLength * cos($angle));
        $needleY = floor($height / 2 + $needleLength * sin($angle));
        $compass = imagecreatefrompng(__DIR__ .'/../../../assets/images/compass.png');
        $kaaba = imagecreatefrompng(__DIR__ .'/../../../assets/images/kaaba.png');
        $kaabaH = imagesy($kaaba);
        $kaabaW = imagesx($kaaba);
        $kaabaX = Utils\Qibla::kaabaX1($degrees, $needleX, $kaabaW, $kaabaH);
        $kaabaY = Utils\Qibla::kaabaY1($degrees, $needleY, $kaabaW, $kaabaH);
        // only if it's a blank image on line 3: imagecolorallocate($compass, 15, 142, 210);
        $greenline = imagecolorallocate($compass, 3, 102, 33);
        imagesetthickness($compass, 21);
        imageline($compass, $width / 2, $height / 2, $needleX, $needleY, $greenline);
        imagecopy($compass, $kaaba, $kaabaX, $kaabaY, 0, 0, $kaabaW, $kaabaH);
        imagecolortransparent($compass, imagecolorallocate($compass, 0, 0, 0));
        if ($size !== $width) {
            $compassResized = $tmp = imagecreatetruecolor($size, $size);
            // imagecopyresized($compassResized, $compass, 0, 0, 0, 0, $size, $size, $width, $height);
            imagecopyresampled($compassResized, $compass, 0, 0, 0, 0, $size, $size, $width, $height);
            imagecolortransparent($compassResized, imagecolorallocate($compass, 0, 0, 0));
        }

        ob_start();
        if ($size !== $width) {
            imagepng($compassResized, null, 9);
        }
        else {
            imagepng($compass);
        }

        $data = ob_get_contents();

        imagedestroy($compass);
        if ($size !== $width) {
            imagedestroy($compassResized);
        }

        imagedestroy($kaaba);
        ob_end_clean();

        return Utils\Response::png($response, $data, 200, [], true, 604800);
    }

}