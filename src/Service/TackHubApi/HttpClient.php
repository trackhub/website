<?php

declare(strict_types=1);

namespace App\Service\TackHubApi;

use App\Service\TackHubApi\Response\Place;
use App\Service\TackHubApi\Response\PlaceImage;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientInterface;
use RuntimeException;

class HttpClient implements Client
{
    private ClientInterface $client;

    public function __construct(string $url)
    {
        $this->client = new \GuzzleHttp\Client([
            'base_uri' => $url,
        ]);
    }

    public function getPlace(string $id): Place
    {
        $body = json_encode([
            'query' => <<<'GRAPHQL'
                query GetPlace($idOrSlug: String!) {
                  place(idOrSlug: $idOrSlug) {
                    id
                    slugOrId
                    slug
                    type
                    name
                    description
                    icon
                    lat
                    lng
                    attraction
                    images {
                        placeId
                        filepath
                    }
                  }
                }
                GRAPHQL,
            'variables' => ['idOrSlug' => $id],
        ], JSON_THROW_ON_ERROR);

        $response = $this->client->sendRequest(
            new Request(
                'POST',
                '/query',
                [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                $body
            )
        );

        $payload = json_decode(
            (string) $response->getBody(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if (!empty($payload['errors'])) {
            throw new RuntimeException('GraphQL getPlace request failed: ' . json_encode($payload['errors']));
        }

        $place = $payload['data']['place'] ?? null;
        if (!is_array($place)) {
            throw new RuntimeException('GraphQL getPlace response did not contain data.place.');
        }

        $images = [];
        foreach ($place['images'] as $image) {
            $images[] = new PlaceImage(
                $image['placeId'],
                $image['filepath'],
            );
        }

        return new Place(
            $place['id'],
            $place['slugOrId'],
            $place['slug'],
            $place['name'],
            $place['icon'] ?? '',
            (float) $place['lat'],
            (float) $place['lng'],
            $place['attraction'],
            $place['type'],
            $place['description'],
            $images
        );
    }
}
