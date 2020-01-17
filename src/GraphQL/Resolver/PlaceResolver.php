<?php

namespace App\GraphQL\Resolver;

use App\Entity\Place;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class PlaceResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Get a place by id
     *
     * @param Argument $args
     * @return Place|null
     */
    public function resolve(Argument $args): ?Place
    {
        return $this->em->find(Place::class, $args['id']);
    }

    public static function getAliases(): array
    {
        return [
            'resolve' => 'Place'
        ];
    }
}