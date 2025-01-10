<?php

namespace Api\Controllers\v1;

use Mamluk\Kipchak\Components\Controllers\Slim;
use Mamluk\Kipchak\Components\Http;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use AlQibla\Calculation;
use OpenApi\Attributes as OA;
use Api\Utils;

#[OA\OpenApi(
    openapi: '3.1.0',
    info: new OA\Info(
        version: 'v1',
        description: "A Qibla direction API to calculate the Qibla angle on a compass and generate a Qibla compass image",
        title: 'Qibla Direction API - AlAdhan'
    ),
    servers: [
        new OA\Server(url: 'https://api.aladhan.com/v1'),
        new OA\Server(url: 'http://api.aladhan.com/v1')
    ],
    tags: [
        new OA\Tag(name: 'Qibla')
    ]

)]
class Qibla extends Slim
{
    #[OA\Get(
        path: '/qibla/{latitude}/{longitude}',
        description: 'Returns the Qibla direction based on a pair of co-ordinates',
        summary: 'Qibla direction API',
        tags: ['Qibla'],
        parameters: [
            new OA\PathParameter(name: 'latitude', description: "Latitude co-ordinates ",
                in: 'path', required: true, schema: new OA\Schema(type: 'number', format: 'float'), example: 19.07101757042149
            ),
            new OA\PathParameter(name: 'longitude', description: "Longitude co-ordinates",
                in: 'path', required: true, schema: new OA\Schema(type: 'number', format: 'float'), example: 72.83862228676163
            )
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns the Qibla direction based on the co-ordinates',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 200),
                            new OA\Property(property: 'status', type: 'string', example: 'OK'),
                            new OA\Property(property: 'data',
                                properties: [
                                    new OA\Property(property: 'latitude', type: 'number', example: 19.07101757042149),
                                    new OA\Property(property: 'longitude', type: 'number', example: 72.83862228676163),
                                    new OA\Property(property: 'direction', type: 'number', example: 280.07746236651514)
                                ], type: 'object')
                        ]
                    )
                )
            )
        ]
    )]
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
            7200,
            ['public']
        );
    }

    #[OA\Get(
        path: '/qibla/{latitude}/{longitude}/compass',
        description: 'Returns a compass image marking the direction of the Qibla',
        summary: 'Qibla direction compass API',
        tags: ['Qibla'],
        parameters: [
            new OA\PathParameter(name: 'latitude', description: "Latitude co-ordinates ",
                in: 'path', required: true, schema: new OA\Schema(type: 'number', format: 'float'), example: 19.07101757042149
            ),
            new OA\PathParameter(name: 'longitude', description: "Longitude co-ordinates",
                in: 'path', required: true, schema: new OA\Schema(type: 'number', format: 'float'), example: 72.83862228676163
            )
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'Returns a compass image with the Qibla direction based on the co-ordinates',
                content: new OA\MediaType(
                    mediaType: 'image/png',
                    schema: new OA\Schema(type: "string", format: "binary"),
                )
            )
        ]
    )]
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

        return Utils\Response::png($response, $data, 200, [], true, 7200);
    }

}