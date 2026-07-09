<?php
namespace Edaha\Entities;

use Edaha\Entities\User;

use DateTime;
use DateInterval;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'user_sessions')]
class UserSession
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    public User $user;

    #[ORM\Column(unique: true)]
    public string $sid;

    #[ORM\Column]
    public DateTime $logged_in_at {
        get {
            return $this->logged_in_at;
        }
    }

    #[ORM\Column]
    public DateTime $last_activity_at;

    public bool $is_active {
        get {
            return new DateTime() < $this->last_activity_at?->add(DateInterval::createFromDateString('60 minutes'));
        }
    }

    public function __construct(User $user, String $sid)
    {
        $this->user = $user;
        $this->sid = $sid;
        $this->logged_in_at = new DateTime();
        $this->last_activity_at = $this->logged_in_at;
    }

    public function newActivity(): void
    {
        $this->last_activity_at = new DateTime();
    }
}
