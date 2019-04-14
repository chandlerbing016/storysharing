<?php

/**
 * This entity is being made primarily for tracking ips for unique views.
 * 
 * This methodology shall be replaced by memecached based temp storage
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Story;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StoryRepository")
 */
class Visitor
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $ip_address; // ipv4 ipv6

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Story", inversedBy="visitors", cascade={"remove"})
     * @ORM\JoinColumn(name="story_id", referencedColumnName="id")
     */
    private $story;

    public function setStory(Story $story): self
    {
        $this->story = $story;

        return $this;
    }

    public function setIpAddress($ip_address): self
    {
        $this->ip_address = $ip_address;

        return $this;
    }

    public function getIpAddress()
    {
        return $this->ip_address;
    }
}
