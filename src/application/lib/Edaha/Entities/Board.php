<?php
namespace Edaha\Entities;
use Edaha\Entities\BoardOption;
use Edaha\Entities\Post;
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
    private Collection $options;

    /** @var Collection<int, Post> */
    #[ORM\OneToMany(targetEntity: Post::class, mappedBy:'board', fetch: 'EXTRA_LAZY')]
    public Collection $posts;

    public function __construct(string $name, string $directory)
    {
        $this->created_at = new DateTime('now');
        $this->options = new ArrayCollection();
        $this->posts = new ArrayCollection();

        $this->name = $name;
        $this->directory = $directory;
    }

    public function setOption(string $name, ?string $value): void
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

    public function getOptions(): Collection
    {
        return $this->options;
    }

    public function addPost(Post $post): void
    {
        $this->posts[] = $post;
    }

    public function __get(string $name)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('name', $name));
        $option = $this->options->matching($criteria)->first();
        if ($option) {
            return $option->value;
        } else {
            return null;
        }
    }
}
