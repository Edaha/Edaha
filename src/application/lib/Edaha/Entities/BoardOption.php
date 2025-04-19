<?php
namespace Edaha\Entities;
use Edaha\Entities\Board;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'board_options')]
class BoardOption
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    public ?int $id = null {
        get {
            return $this->id;
        }
    }

    #[ORM\ManyToOne(targetEntity: Board::class, inversedBy: 'options')]
    #[ORM\JoinColumn(name: 'board_id', referencedColumnName: 'id')]
    public ?Board $board = null;

    #[ORM\Column]
    public string $name {
        get {
            return $this->name;
        }
        set {
            $this->name = $value;
        }
    }

    #[ORM\Column(nullable: true)]
    public ?string $value {
        get {
            return $this->value;
        }
        set {
            $this->value = $value;
        }
    }

    public function __construct($board, $name, $value)
    {
        $this->board = $board;
        $this->name = $name;
        $this->value = $value;
    }
}
