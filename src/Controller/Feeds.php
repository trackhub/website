<?php

namespace App\Controller;

use Eko\FeedBundle\Feed\FeedManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\HttpFoundation\Response;

class Feeds extends AbstractController
{
    private const FEEDS = [
        'places' => \App\Entity\Place::class,
    ];

    /**
     * Generate RSS or Atom feed
     *
     * @param string $feedName
     * @param FeedManager $feedManager
     * @param AdapterInterface $adapter
     * @return Response

     */
    public function feeds(string $feedName, FeedManager $feedManager, AdapterInterface $adapter) : Response
    {
        /**
         * This will throw InvalidArgumentException if the feed name is invalid. Thus
         * there is no need to double check for valid argument.
         */
        $feed = $feedManager->get($feedName);

        /**
         * Check for existing cache
         */
        $item = $adapter->getItem($feedName);

        if (!$item->isHit()) {
            $item->set($this->getDoctrine()->getRepository(self::FEEDS[$feedName])->findAll());
            $adapter->save($item);
        }
        $items = $item->get();
        $feed->addFromArray(array_slice($items, 0, 10)) ;

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
