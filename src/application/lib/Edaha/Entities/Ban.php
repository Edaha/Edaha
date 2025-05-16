<?php
namespace Edaha\Entities;

use DateTime;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: BanRepository::class)]
#[ORM\Table(name: 'bans')]
class Ban
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
    public string $ip {
        get {
            return $this->ip;
        }
        set {
            $this->ip = $value;
        }
    }

    #[ORM\Column]
    public string $reason {
        get {
            return $this->reason;
        }
        set {
            $this->reason = $value;
        }
    }

    #[JoinTable(name: 'ban_boards')]
    #[JoinColumn(name: 'ban_id', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'board_id', referencedColumnName: 'id', unique: true)]
    #[ManyToMany(targetEntity: 'Board')]
    public Collection $boards {
        get {
            return $this->boards;
        }
    }

    #[ORM\Column]
    public ?string $staff_note = null {
        get {
            return $this->staff_note;
        }
        set {
            $this->staff_note = $value;
        }
    }

    #[ORM\Column]
    public DateTime $created_at {
        get {
            return $this->created_at;
        }
    }

    #[ORM\Column]
    public ?DateTime $expires_at {
        get {
            return $this->expires_at;
        }
        set {
            $this->expires_at = $value;
        }
    }

    public bool $is_expired {
        get {
            return $this->expires_at !== null && $this->expires_at < new DateTime();
        }
    }

    #[ORM\Column]
    public bool $allow_read = true {
        get {
            return $this->allow_read;
        }
        set {
            $this->allow_read = $value;
        }
    }

    #[ORM\Column]
    public bool $allow_appeal = true {
        get {
            return $this->allow_appeal;
        }
        set {
            $this->allow_appeal = $value;
        }
    }

    public function __construct(string $ip, string $reason, ?DateTime $expires_at = null)
    {
        $this->ip = $ip;
        $this->reason = $reason;
        $this->created_at = new DateTime();
        $this->expires_at = $expires_at;
        $this->boards = new ArrayCollection();
    }

    public function expire(): void
    {
        $this->expires_at = new DateTime();
    }
}
