<?php

declare(strict_types=1);

namespace App\Service\TackHubApi;

use App\Service\TackHubApi\Response\Place;

interface Client
{
    function getPlace(string $id): Place;
}