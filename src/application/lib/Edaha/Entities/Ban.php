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
    }

    #[ORM\Column]
    public string $reason {
        get {
            return $this->reason;
        }
    }

    #[JoinTable(name: 'ban_boards')]
    #[JoinColumn(name: 'ban_id', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'board_id', referencedColumnName: 'id', unique: true)]
    #[ManyToMany(targetEntity: 'Board')]
    public Collection $boards {
        get {
            if (!isset($this->boards)) {
                $this->boards = new ArrayCollection();
            }
            return $this->boards;
        }
    }

    #[ORM\Column]
    public bool $is_global = false {
        get {
            return $this->is_global;
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
    public bool $allow_read {
        get {
            return $this->allow_read;
        }
        set {
            $this->allow_read = $value;
        }
    }

    #[ORM\Column]
    public bool $allow_appeal {
        get {
            return $this->allow_appeal;
        }
        set {
            $this->allow_appeal = $value;
        }
    }

    #[ORM\Embedded(class: BanAppeal::class)]
    public BanAppeal $appeal;

    public function __construct(
        string $ip,
        string $reason,
        ?DateTime $expires_at = null,
        ?string $staff_note = null,
        ?bool $allow_read = true,
        ?bool $allow_appeal = true,
    ) {
        $this->ip = $ip;
        $this->reason = $reason;
        $this->expires_at = $expires_at;
        $this->staff_note = $staff_note;

        $this->allow_read = $allow_read;
        $this->allow_appeal = $allow_appeal;
        
        $this->created_at = new DateTime();
        $this->boards = new ArrayCollection();
    }

    public function expire(): void
    {
        $this->expires_at = new DateTime();
    }

    public function addBoard(Board $board): void
    {
        if (!$this->boards->contains($board)) {
            $this->boards->add($board);
        }
    }

    public function removeBoard(Board $board): void
    {
        if ($this->boards->contains($board)) {
            $this->boards->removeElement($board);
        }
    }

    public function isBannedFromBoard(Board $board): bool
    {
        return ($this->is_global) ? true : $this->boards->contains($board);
    }

    public function banGlobally(): void
    {
        $this->is_global = true;
        $this->boards->clear();
    }

    public function appealBan(string $message): void
    {
        if ($this->allow_appeal) {
            $this->appeal = new BanAppeal($message);
        } else {
            throw new \Exception('Appeal not allowed');
        }
    }

    public function approveAppeal(): void
    {
        if ($this->allow_appeal) {
            $this->expire();
            $this->appeal->is_approved = true;
        } else {
            throw new \Exception('Appeal not allowed');
        }
    }

    public static function banIp(string $ip, string $reason, ?DateTime $expires_at = null, ?string $staff_note = null): Ban
    {
        return new Ban($ip, $reason, $expires_at, $staff_note);
    }

    public static function banIpGlobally(string $ip, string $reason, ?DateTime $expires_at = null, ?string $staff_note = null): Ban
    {
        $ban = new Ban($ip, $reason, $expires_at, $staff_note);
        $ban->banGlobally();
        return $ban;
    }
}

#[ORM\Embeddable]
class BanAppeal 
{
    #[ORM\Column(nullable: true)]
    public ?string $message = null;

    #[ORM\Column]
    public DateTime $submitted_at;

    #[ORM\Column]
    public bool $is_approved = false {
        get {
            return $this->is_approved;
        }
    }

    public function __construct(
        string $message,
    ) {
        $this->message = $message;
        $this->submitted_at = new DateTime();
    }
}
