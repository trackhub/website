<?php


namespace App\Entity\Place;


trait PlaceTrait
{

    /**
     * This method returns feed item title.
     *
     *
     * @return string
     */
    public function getFeedItemTitle()
    {
        return $this->getNameEn();
    }

    /**
     * This method returns feed item description (or content).
     *
     *
     * @return string
     */
    public function getFeedItemDescription()
    {
        return "ASDSDsd";
    }

    /**
     * This method returns feed item URL link.
     *
     *
     * @return string
     */
    public function getFeedItemLink()
    {
        return $this->id;
    }

    /**
     * This method returns item publication date.
     *
     *
     * @return \DateTime
     */
    public function getFeedItemPubDate()
    {
        return $this->createdAt;
    }

}