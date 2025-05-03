<?php
namespace Edaha\Entities;

use Edaha\Entities\PostAttachment;
use Edaha\Entities\Board;
use Edaha\Entities\PostRepository;

use DateTime;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\Table(name: 'posts')]
class Post
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    public ?int $id = null {
        get {
            return $this->id;
        }
    }

    #[ORM\ManyToOne(targetEntity: Board::class, inversedBy: 'posts')]
    #[ORM\JoinColumn(name: 'board_id', referencedColumnName: 'id', nullable: false)]
    public Board $board {
        get {
            return $this->board;
        }
        set {
            $this->board = $value;
        }
    }

    #[ORM\Column(nullable: true)]
    public ?string $subject = null {
        get {
            return $this->subject;
        }
        set {
            $this->subject = $value;
        }
    }

    #[ORM\Column]
    public ?string $message = null  {
        get {
            return $this->message;
        }
        set {
            $this->message = $value;
        }
    }

    #[ORM\Column]
    public ?DateTime $created_at {
        get {
            return $this->created_at;
        }
    }

    #[ORM\Column(nullable: true)]
    public ?DateTime $locked_at = null {
        get {
            return $this->locked_at;
        }
        set {
            $this->locked_at = $value;
        }
    }

    public bool $is_locked {
        get {
            return isset($this->locked_at);
        }
    }

    #[ORM\Column(nullable: true)]
    public ?DateTime $stickied_at = null {
        get {
            return $this->stickied_at;
        }
        set {
            $this->stickied_at = $value;
        }
    }

    public bool $is_stickied {
        get {
            return isset($this->stickied_at);
        }
    }

    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'parent', fetch: 'EXTRA_LAZY')]
    #[ORM\OrderBy(['created_at' => 'ASC'])]
    public Collection $replies;

    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'replies')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id')]
    public ?Post $parent = null;

    public bool $is_thread {
        get {
            return $this->parent === null;
        }
    }
    public bool $is_reply {
        get {
            return $this->parent !== null;
        }
    }

    #[ORM\Column(nullable: true)]
    public ?DateTime $bumped_at = null {
        get {
            return $this->bumped_at;
        }
    }

    #[ORM\OneToMany(targetEntity: PostAttachment::class, mappedBy: 'post', cascade: ['persist', 'remove'])]
    public Collection $attachments {
        get {
            return $this->attachments;
        }
    }

    #[ORM\Embedded(class: Poster::class)]
    public Poster $poster;

    public function __construct(Board $board, string $message, ?string $subject = null, ?Post $parent = null)
    {
        $this->board = $board;
        $this->message = $message;
        $this->subject = $subject;
        $this->parent = $parent;

        $this->created_at = new DateTime('now');
        $this->poster = new Poster();
        $this->replies = new ArrayCollection();
        $this->attachments = new ArrayCollection();

        $this->board->addPost($this);
        if (!is_null($parent)) {
            $this->parent->addReply($this);
        }
        if (is_null($parent)) {
            $this->bump();
        }
    }

    public function addReply(Post $reply): void
    {
        if ($this->is_locked) {
            throw new \Exception('Cannot add reply to a locked post');
        } elseif (!is_null($this->parent)) {
            throw new \Exception('Cannot add reply to a reply post');
        } elseif ($this->replies->contains($reply)) {
            throw new \Exception('Reply already exists');
        } else {
            $this->replies[] = $reply;
        }
    }

    public function addAttachment(PostAttachment $attachment): void
    {
        $this->attachments[] = $attachment;
    }

    public function getAttachments(): Collection
    {
        return $this->attachments;
    }

    public function getAllReplies(): Collection
    {
        return $this->replies;
    }

    public function getFirstNReplies(int $n): Array
    {
        return $this->replies->slice(0, $n);
    }

    public function getLastNReplies(int $n): Array
    {
        $criteria = Criteria::create()
            ->orderBy(['created_at' => 'DESC'])
            ->setMaxResults($n);
        $lastReplies = $this->replies->matching($criteria);
        $lastReplies = $lastReplies->toArray();
        $lastReplies = array_reverse($lastReplies);
        return $lastReplies;
    }

    public function sticky(): void
    {
        if ($this->is_stickied) {
            throw new \Exception('Post is already stickied');
        }
        if ($this->is_reply) {
            throw new \Exception('Cannot sticky a reply post'); // TODO: But what if we could?
        }
        $this->stickied_at = new DateTime('now');
    }

    public function unsticky(): void
    {
        if (!$this->is_stickied) {
            throw new \Exception('Post is not stickied');
        }
        $this->stickied_at = null;
    }

    public function lock(): void
    {
        if ($this->is_locked) {
            throw new \Exception('Post is already locked');
        }
        if ($this->is_reply) {
            throw new \Exception('Cannot lock a reply post');
        }
        $this->locked_at = new DateTime('now');
    }

    public function unlock(): void
    {
        if (!$this->is_locked) {
            throw new \Exception('Post is not locked');
        }
        $this->locked_at = null;
    }

    public function bump(): void
    {
        if ($this->is_reply) {
            throw new \Exception('Cannot bump a reply post');
        }
        if ($this->is_locked) {
            throw new \Exception('Cannot bump a locked post');
        }
        $this->bumped_at = new DateTime('now');
    }

    public function getPosterDisplayName(): string
    {
        if ($this->poster->name === null || $this->poster->name === '' || $this->board->forced_anonymous) {
            return (isset($this->board->anonymous)) ? $this->board->anonymous : 'Anonymous';
        } else {
            return $this->poster->name;
        }
    }
}

#[ORM\Embeddable]
class Poster 
{
    #[ORM\Column(nullable: true)]
    public ?string $name = null;

    #[ORM\Column(nullable: true)]
    public ?string $email = null;
    
    #[ORM\Column]
    public string $ip = '127.0.0.1';
}
