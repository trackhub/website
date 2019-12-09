<?php

namespace App\Entity\Track;

use App\Entity\Language;
use App\Entity\Track;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="track_tr")
 */
class TrackTranslation
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\Track",
     *     inversedBy="translations"
     * )
     * @ORM\JoinColumn(
     *     name="track_id",
     *     referencedColumnName="id",
     *     nullable=false
     * )
     */
    private $track;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\Language",
     *     inversedBy="trackTranslations"
     * )
     * @ORM\JoinColumn(
     *     name="language_id",
     *     referencedColumnName="id",
     *     nullable=false
     * )
     */
    private $language;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Track
     */
    public function getTrack(): Track
    {
        return $this->track;
    }

    /**
     * @param Track $track
     */
    public function setTrack(Track $track): void
    {
        $this->track = $track;
    }

    /**
     * @return Language
     */
    public function getLanguage(): Language
    {
        return $this->language;
    }

    /**
     * @param Language $language
     */
    public function setLanguage(Language $language): void
    {
        $this->language = $language;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
