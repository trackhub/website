<?php

namespace App\Event\Listener;

use App\Contract\Entity\DescribableInterface;
use App\Html\Purifier;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;

class HtmlPurifySubscriber implements EventSubscriber
{
    private Purifier $purifier;

    public function __construct(Purifier $purifier)
    {
        $this->purifier = $purifier;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();

        if ($entity instanceof DescribableInterface) {
            $this->purifyEntity($entity);
        }
    }

    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();

        if ($entity instanceof DescribableInterface) {
            $this->purifyEntity($entity);
        }
    }

    public function purifyEntity(DescribableInterface $entity)
    {
        if ($entity->getDescriptionBg()) {
            $entity->setDescriptionBg(
                $this->purifier->purify($entity->getDescriptionBg())
            );
        }

        if ($entity->getDescriptionEn()) {
            $entity->setDescriptionEn(
                $this->purifier->purify($entity->getDescriptionEn())
            );
        }
    }
}
