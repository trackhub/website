<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LanguageRepository")
 */
class Language
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=6, unique=true)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $name_en;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $name;


    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Track\TrackTranslation",
     *     mappedBy="language",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     *     )
     */
    private $trackTranslations;

    public function __construct()
    {
        $this->trackTranslations = new ArrayCollection();
    }

    /**
     * Get language id
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get language code
     *
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Set english name
     *
     * @return string
     */
    public function getNameEn(): string
    {
        return $this->name_en;
    }

    /**
     * Get native name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
