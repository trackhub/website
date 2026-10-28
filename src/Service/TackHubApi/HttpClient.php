<?php

declare(strict_types=1);

namespace App\Service\TackHubApi;

use App\Service\TackHubApi\Response\Place;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientInterface;

class HttpClient implements Client
{
    private ClientInterface $client;

    public function __construct(string $url) {
        $this->client = new \GuzzleHttp\Client([
            'base_uri' => $url,
        ]);

    }

    public function getPlace(string $id): Place {
        $response = $this->client->sendRequest(
            new Request(
                'GET',
                '/',
                [],
                '
                    {
                      place(idOrSlug:"' . $id . '") {
                        id
                        slugOrId
                        name
                        icon
                        
                        lat
                        lng
                        attraction
                      }
                    }
                '
            )
        );

        return new Place();
    }
}