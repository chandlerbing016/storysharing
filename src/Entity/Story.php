<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\User;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StoryRepository")
 */
class Story
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\Column(type="integer")
     */
    private $viewCounter = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $upvoteCounter = 0;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="upvotingStories")
     */
    private $upvotingUsers;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="stories")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Visitor", mappedBy="story", cascade={"remove"})
     */
    private $visitors;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="story", cascade={"remove"})
     */
    private $comments;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Tag", inversedBy="stories")
     * @ORM\JoinTable(name="stories_tags")
     */
    private $tags;

    public function __construct()
    {
        $this->visitors = new ArrayCollection();
        $this->upvotingUsers = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getContent():? string
    {
        return $this->content;
    }

    public function raiseViewCounter(): self
    {
        $this->viewCounter++;

        return $this;
    }

    public function getViewCounter(): int
    {
        return $this->viewCounter;
    }

    public function getUpvotingUsers()
    {
        return $this->upvotingUsers;
    }

    public function raiseUpvoteCounter(): self
    {
        $this->upvoteCounter++;

        return $this;
    }

    public function getUpvoteCounter(): int
    {
        return $this->upvoteCounter;
    }

    public function setUser(User $user): self
    {
        if(!isset($this->user)) {
            $this->user = $user;
            $user->setStory($this);
        }

        return $this;
    }

    public function getUser():? User
    {
        return $this->user;
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function setTag(Tag $tag)
    {
        if(!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }
    }

    public function getTags()
    {
        return $this->tags;
    }
}
