<?php
namespace Edaha\Entities;

use Edaha\Entities\Board;

use DateTime;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

#[ORM\Entity]
#[ORM\Table(name: 'sections')]
class Section
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    public ?int $id = null {
        get {
            return $this->id;
        }
    }

    #[ORM\Column(unique: true)]
    public string $name;

    #[ORM\Column]
    public bool $is_hidden;

    #[ORM\Column]
    public DateTime $created_at;
    
    #[ORM\OneToMany(targetEntity: Board::class, mappedBy: 'section', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'section_id', referencedColumnName: 'id')]
    public Collection $boards;

    public function __construct(string $name, bool $hidden)
    {
        $this->name = $name;
        $this->is_hidden = $hidden;
        $this->created_at = new DateTime();
        $this->boards = new ArrayCollection();
    }

    public function addBoard(Board $board): void
    {
        $this->boards[] = $board;
        $board->section = $this;
    }

    public function removeBoard(Board $board): void
    {
        $this->boards->removeElement($board);
        $board->section = null;
    }
}