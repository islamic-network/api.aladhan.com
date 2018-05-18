<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use AlAdhanApi\Helper\Response as ApiResponse;
use AlAdhanApi\Helper\Request as ApiRequest;
use AlAdhanApi\Model\AsmaAlHusna;


$app->group('/v1', function() {
    /**
     *
     * @api {get} http://api.aladhan.com/v1/asmaAlHusna/:numbers All or Multiple Names
     * @apiName GetMultiAsmaAlHusna
     * @apiDescription Includes the Arabic text with transliteration and meaning of each name.
     * @apiGroup AsmaAlHusna
     * @apiVersion 1.0.1
     *
     * @apiParam {string{number{1-99},number{1-99},number{1-99}}} [numbers] Names are numbered from 1 to 99, in the order usually recited
     * in the Islamic tradition. They start with 1 (Ar Rahmaan) and end with 99 (As
     * Saboor). If not specified, all names will be returned.
     *
     * @apiExample {http} Example usage:
     *   http://api.aladhan.com/v1/asmaAlHusna/1,2
     *
     * @apiSuccessExample Success-Response:
     * HTTP/1.1 200 OK
     *   {
     *       code: 200,
     *       status: "OK",
     *       data: [
     *       {
     *           name: "الرَّحْمَنُ",
     *           transliteration: "Ar Rahmaan",
     *           number: 1,
     *           en: {
     *               meaning: "The Beneficent"
     *           }
     *       },
     *       {
     *           name: "الرَّحِيمُ",
     *           transliteration: "Ar Raheem",
     *           number: 2,
     *           en: {
     *               meaning: "The Merciful"
     *           }
     *       }
     *       ...
     *       ]
     *   }
     *
     * @apiError InvalidNumber Number must be between 1 and 99.
     *
     * @apiErrorExample Error-Response:
     * HTTP/1.1 404 Not Found
     * {
     * "code": 400,
     * "status": "Bad Request",
     * "data": "Please specify a valid number between 1 and 99."
     * }
     */
    $this->get('/asmaAlHusna', function (Request $request, Response $response) {
        //$this->helper->logger->write();
        $names = AsmaAlHusna::get();

        return $response->withJson(ApiResponse::build($names, 200, 'OK'), 200);

    });

    /**
     * @api {get} http://api.aladhan.com/v1/asmaAlHusna/:number Single Name.
     * @apiDescription Includes the Arabic text with transliteration and meaning.
     * @apiName GetAsmaAlHusna
     * @apiGroup AsmaAlHusna
     * @apiVersion 1.0.1
     *
     * @apiParam {number{1-99}} number  Names are numbered from 1 to 99, in the order usually recited
     * in the Islamic tradition. They start with 1 (Ar Rahmaan) and end with 99 (As
     * Saboor).
     *
     * @apiExample {http} Example usage:
     *   http://api.aladhan.com/v1/asmaAlHusna/77
     *
     * @apiSuccessExample Success-Response:
     * HTTP/1.1 200 OK
     * {
     *    code: 200,
     *    status: "OK",
     *    data: [
     *        {
     *            name: "الْوَالِي",
     *            transliteration: "Al Waali",
     *            number: 77,
     *            en: {
     *                meaning: "The Governor"
     *            }
     *        }
     *    ]
     * }
     *
     * @apiError InvalidNumber Number must be between 1 and 99.
     *
     * @apiErrorExample Error-Response:
     * HTTP/1.1 404 Not Found
     * {
     * "code": 400,
     * "status": "Bad Request",
     * "data": "Please specify a valid number between 1 and 99."
     * }
     */
    $this->get('/asmaAlHusna/{no}', function (Request $request, Response $response) {
        //$this->helper->logger->write();
        $number = $request->getAttribute('no');
        $number = explode(',', $number);
        $nos = [];
        foreach ($number as $no) {
            $nos[] = (int) $no;
        }
        $names = AsmaAlHusna::get($nos);

        if ($names == false) {
            return $response->withJson(ApiResponse::build('Please specify a valid number between 1 and 99', 400, 'Bad Request'), 400);
        }

        return $response->withJson(ApiResponse::build($names, 200, 'OK'), 200);

    });
});
