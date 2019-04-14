<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TagRepository")
 * @UniqueEntity("title")
 */
class Tag
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    private $title;

    /**
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $isApproved = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="tagSuggestions")
     * @ORM\JoinColumn(name="user_id")
     */
    private $tagSuggestor;

    /**
     * users subscribed to this tag
     * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="tags")
     */
    private $users;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Story", mappedBy="tags")
     */
    private $stories;

    public function __construct()
    {
        $this->stories = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    public function approve(): self
    {
        $this->isApproved = true;

        return $this;
    }

    public function disapprove(): self
    {
        $this->isApproved = false;

        return $this;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle():? string
    {
        return $this->title;
    }

    public function setTagSuggestor(User $tagSuggestor): self
    {
        $this->tagSuggestor = $tagSuggestor;

        return $this;
    }

    public function getStories()
    {
        return $this->stories;
    }

    public function getUsers()
    {
        return $this->users;
    }
}
