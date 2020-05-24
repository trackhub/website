<?php

namespace App\Event\Listener;

use App\Entity\Track;
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

        if ($entity instanceof Track) {
            $this->purifyTrack($entity);
        }
    }

    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();

        if ($entity instanceof Track) {
            $this->purifyTrack($entity);
        }
    }

    public function purifyTrack(Track $track)
    {
        if ($track->getDescriptionBg()) {
            $track->setDescriptionBg(
                $this->purifier->purify($track->getDescriptionBg())
            );
        }

        if ($track->getDescriptionEn()) {
            $track->setDescriptionEn(
                $this->purifier->purify($track->getDescriptionEn())
            );
        }
    }
}
