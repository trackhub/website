<?php

namespace App\GraphQL\Resolver;

use App\Entity\Place;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class PlacesResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function resolve(Argument $args)
    {
        $places = $this->em
            ->getRepository(Place::class)
            ->findBy(
                [],
                [
                    'createdAt' => 'desc'
                ],
                $args['limit'],
                0
            );

        return ['places' => $places];
    }

    public static function getAliases(): array
    {
        return [
            'resolve' => 'Places'
        ];
    }

}