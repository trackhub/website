<?php

namespace App\Entity\Place;

trait FeedTrait
{
    /**
     * This method returns feed item title.
     *
     * @return string
     */
    public function getFeedItemTitle() : string
    {
        return $this->getNameEn();
    }

    /**
     * This method returns feed item description (or content).
     *
     * @return string
     */
    public function getFeedItemDescription() : string
    {
        return "Dummy content";
    }

    /**
     * This method returns item publication date.
     *
     * @return \DateTime
     */
    public function getFeedItemPubDate() : \DateTime
    {
        return $this->createdAt;
    }

    /**
     * This method returns the name of the route.
     *
     * @return string
     */
    public function getFeedItemRouteName() : string
    {
        return 'app_place_view';
    }

    /**
     * This method returns the parameters for the route.
     *
     * @return array
     */
    public function getFeedItemRouteParameters() : array
    {
        return [
            'id' => $this->id,
        ];
    }

    /**
     * This method returns the anchor to be appended on this item's url.
     *
     * @return string The anchor, without the "#"
     */
    public function getFeedItemUrlAnchor() : string
    {
        return "";
    }
}