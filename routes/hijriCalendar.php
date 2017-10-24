<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use AlAdhanApi\Helper\Response as ApiResponse;
use AlAdhanApi\Helper\Request as ApiRequest;
use AlAdhanApi\Model\HijriCalendarService;

$app->get('/gToHCalendar/{month}/{year}', function (Request $request, Response $response) {
    $this->helper->logger->write();
    $cs = new HijriCalendarService();

    $y = (int) $request->getAttribute('year');
    $m = (int) $request->getAttribute('month');


    return $response->withJson(ApiResponse::build($cs->getGToHCalendar($m, $y), 200, 'OK'), 200);
});

$app->get('/hToGCalendar/{month}/{year}', function (Request $request, Response $response) {
    $this->helper->logger->write();
    $cs = new HijriCalendarService();

    $y = (int) $request->getAttribute('year');
    $m = (int) $request->getAttribute('month');

    return $response->withJson(ApiResponse::build($cs->getHtoGCalendar($m, $y), 200, 'OK'), 200);
});


$app->get('/gToH', function (Request $request, Response $response) {
    $this->helper->logger->write();
    $date = $request->getQueryParam('date') == '' || null ? date('d-m-Y', time()) : $request->getQueryParam('date');
    $hs = new HijriCalendarService();
    $result = $hs->gToH($date);
    if ($result) {
        return $response->withJson(ApiResponse::build($result, 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Invalid date or unable to convert it', 400, 'Bad Request'), 400);
    }
});

$app->get('/hToG', function (Request $request, Response $response) {
    $this->helper->logger->write();
    $hs = new HijriCalendarService();
    if ($request->getQueryParam('date') == '' || null) {
        $date = date('d-m-Y', time());
        $fs = $hs->gToH($date);
        $date = $fs['hijri']['date'];
    } else {
        $date = $request->getQueryParam('date');
    }
    $result = $hs->hToG($date);
    if ($result) {
        return $response->withJson(ApiResponse::build($result, 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Invalid date or unable to convert it.', 400, 'Bad Request'), 400);
    }
});

$app->get('/nextHijriHoliday', function (Request $request, Response $response) {
    $this->helper->logger->write();
    $hs = new HijriCalendarService();
    $result = $hs->nextHijriHoliday();;
    if ($result) {
        return $response->withJson(ApiResponse::build($result, 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Unable to compute next holiday.', 400, 'Bad Request'), 400);
    }
});

$app->get('/currentIslamicYear', function (Request $request, Response $response) {
    $this->helper->logger->write();
    $hs = new HijriCalendarService();
    $result = $hs->getCurrentIslamicYear();
    if ($result) {
        return $response->withJson(ApiResponse::build($result, 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Unable to compute year.', 400, 'Bad Request'), 400);
    }
});

$app->get('/currentIslamicMonth', function (Request $request, Response $response) {
    $this->helper->logger->write();
    $hs = new HijriCalendarService();
    $result = $hs->getCurrentIslamicMonth();
    if ($result) {
        return $response->withJson(ApiResponse::build($result, 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Unable to compute year.', 400, 'Bad Request'), 400);
    }
});

$app->get('/islamicYearFromGregorianForRamadan/{year}', function (Request $request, Response $response) {
    $this->helper->logger->write();
    $y = (int) $request->getAttribute('year');
    $hs = new HijriCalendarService();
    $result = $hs->getIslamicYearFromGregorianForRamadan($y);
    if ($result) {
        return $response->withJson(ApiResponse::build($result, 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Unable to compute year.', 400, 'Bad Request'), 400);
    }
});

$app->get('/hijriHolidays/{day}/{month}', function (Request $request, Response $response) {
    $this->helper->logger->write();
    $d = (int) $request->getAttribute('day');
    $m = (int) $request->getAttribute('month');
    $hs = new HijriCalendarService();
    $result = $hs->getHijriHolidays($d, $m);
    if ($result !== false) {
        return $response->withJson(ApiResponse::build($result, 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Please specify a valid day and month. Example, 23 and 11.', 400, 'Bad Request'), 400);
    }
});

$app->get('/specialDays', function (Request $request, Response $response) {
    $this->helper->logger->write();
    $hs = new HijriCalendarService();
    $result = $hs->specialDays();
    if ($result) {
        return $response->withJson(ApiResponse::build($result, 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Something went wrong. Please try again later. Sorry.', 400, 'Bad Request'), 400);
    }
});
    
$app->get('/islamicMonths', function (Request $request, Response $response) {
    $this->helper->logger->write();
    $hs = new HijriCalendarService();
    $result = $hs->getIslamicMonths();
    if ($result) {
        return $response->withJson(ApiResponse::build($result, 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Something went wrong. Please try again later. Sorry.', 400, 'Bad Request'), 400);
    }
});
