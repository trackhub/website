<?php

namespace App\Controller;

use Eko\FeedBundle\Feed\FeedManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class Feeds extends AbstractController
{
    private const FEEDS = [
        'places' => \App\Entity\Place::class,
    ];

    /**
     * Generate RSS feed
     *
     * @param string $feedName
     * @param FeedManager $feedManager
     * @param CacheInterface $feedsCache
     * @return Response
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function feeds(string $feedName, FeedManager $feedManager, CacheInterface $feedsCache) : Response
    {
        /**
         * This will throw InvalidArgumentException if the feed name is invalid. Thus
         * there is no need to double check for valid argument.
         */
        $feed = $feedManager->get($feedName);

        /**
         * Check for existing cache
         */
        $items = $feedsCache->get($feedName, function (ItemInterface $item) {
            $item->expiresAfter(3600);

            return $this->getDoctrine()->getRepository(self::FEEDS[$item->getKey()])->findAll();
        });

        $feed->addFromArray($items) ;

        /**
         * Again skip $format validation and the one provided by the bundle.
         */
        return new Response(
            $feed->render('rss'),
            Response::HTTP_OK,
            [
                'content-type' => 'application/rss+xml',
            ]);
    }
}
