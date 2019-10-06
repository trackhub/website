<?php

namespace App\Contract\Http;

class ApiResponseInterface
{
    public const STATUS_OK = 0;
    // 1 is old status 'ok'. Don't use 1 for now
    public const STATUS_TOO_MANY_ROWS_FOUND = 2;
    public const STATUS_ERROR = 3;
}
