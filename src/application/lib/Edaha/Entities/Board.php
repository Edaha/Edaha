<?php
namespace Edaha\Entities;

use Edaha\Entities\BoardOption;
use Edaha\Entities\Post;
use Edaha\Entities\AttachmentType;
use Edaha\Entities\Section;

use DateTime;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

#[ORM\Entity(repositoryClass: BoardRepository::class)]
#[ORM\Table(name: 'boards')]
class Board
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    public ?int $id = null {
        get {
            return $this->id;
        }
    }

    #[ORM\Column]
    public string $name {
        get {
            return $this->name;
        }
        set {
            $this->name = $value;
        }
    }

    #[ORM\Column(unique: true)]
    public string $directory {
        get {
            return $this->directory;
        }
    }

    #[ORM\Column]
    public DateTime|null $created_at = null {
        get {
            return $this->created_at;
        }
    }

    /** @var Collection<int, BoardOption> */
    #[ORM\OneToMany(targetEntity: BoardOption::class, mappedBy:'board', cascade: ['persist'])]
    public Collection $options {
        get {
            return $this->options;
        }
    }

    /** @var Collection<int, Post> */
    #[ORM\OneToMany(targetEntity: Post::class, mappedBy:'board', fetch: 'EXTRA_LAZY')]
    public Collection $posts;

    /** @var Collection<int, AttachmentType> */
    #[ORM\JoinTable(name: 'boards_attachment_types')]
    #[ORM\JoinColumn(name: 'board_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'attachment_type_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: AttachmentType::class)]
    public Collection $attachment_types {
        get {
            return $this->attachment_types;
        }
    }

    #[ORM\ManyToOne(targetEntity: Section::class, inversedBy: 'boards')]
    #[ORM\JoinColumn(name: 'section_id', referencedColumnName: 'id')]
    public ?Section $section = null {
        get {
            return $this->section;
        }
        set {
            $this->section = $value;
        }
    }
    
    public function addAttachmentType(AttachmentType $attachmentType): void
    {
        if (!$this->attachment_types->contains($attachmentType)) {
            $this->attachment_types[] = $attachmentType;
        }
    }

    public function __construct(string $name, string $directory)
    {
        $this->created_at = new DateTime('now');
        $this->options = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->attachment_types = new ArrayCollection();

        $this->name = $name;
        $this->directory = $directory;
    }

    private function setOption(string $name, ?string $value): void
    {
        // Check if the option already exists
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('name', $name));
        $existingOption = $this->options->matching($criteria)->first();
        if ($existingOption) {
            // Update the existing option
            $existingOption->value = $value;
        } else {
            // Create a new option
            $this->options[] = new BoardOption($this, $name, $value);
        }
    }

    public function addPost(Post $post): void
    {
        $this->posts[] = $post;
    }

    public function getAllThreads(): Collection
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('parent', null))
            ->orderBy(['stickied_at' => 'DESC'])
            ->orderBy(['created_at' => 'DESC']);
        return $this->posts->matching($criteria);
    }

    public function getPaginatedThreads(int $page, int $perPage): Collection
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('parent', null))
            ->orderBy(['stickied_at' => 'DESC'])
            ->orderBy(['created_at' => 'DESC'])
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);
        return $this->posts->matching($criteria);
    }

    public function __get(string $name)
    {
        // Doctrine does some magic with properties that end up skipping the setter/getter hooks, so we need to check for those first.
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('name', $name));
        $option = $this->options->matching($criteria)->first();
        if ($option) {
            return $option->value;
        } else {
            return null;
        }
    }

    public function __set(string $name, $value): void {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        } else {
            $this->setOption($name, $value);
        }
    }
}
