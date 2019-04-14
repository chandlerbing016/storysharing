<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 */

class Notification
{

    const NOTIFICATION_TYPE = ['upvote', 'comment', 'view'];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $message;

    /**
     * @ORM\Column(type="string")
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="notifications")
     * @ORM\JoinColumn(name="user_id")
     */
    private $user;

    /**
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $isSeen = false;

    public function getId()
    {
        return $this->id;
    }
}