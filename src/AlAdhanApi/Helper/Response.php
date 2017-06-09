<?php

namespace AlAdhanApi\Helper;

class Response
{
    
    public static function build($data, $code, $status)
    {
        return
            [
                'code' => $code,
                'status' => $status,
                'data' => $data
            ];
    }
}
