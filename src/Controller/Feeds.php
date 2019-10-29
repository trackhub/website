<?php

namespace App\Controller;

use Eko\FeedBundle\Feed\FeedManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class Feeds extends AbstractController
{
    private const FEEDS = [
        'places' => \App\Entity\Place::class,
    ];
    /**
     * Generate RSS or Atom feed
     *
     * @param $format
     * @param $feed
     * @param FeedManager $feedManager
     * @return Response
     *
     * TODO: Add translations
     */
    public function feeds(string $format, string $feed, FeedManager $feedManager) : Response
    {
        /**
         * This will throw InvalidArgumentException if the feed name is invalid. Thus
         * there is no need to double check for valid argument.
         */
        $feed = $feedManager->get($feed);

        $places = $this->getDoctrine()->getRepository(self::FEEDS[$feed])->findAll();
        $feed->addFromArray($places);

        /**
         * Again skip $format validation and the one provided by the bundle.
         */
        return new Response($feed->render($format));
    }
}
