<?php
namespace Edaha\Entities;

use DateTime;
use DateInterval;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User
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
    public string $username;

    #[ORM\Column]
    private string $password {
        get {
            return $this->password;
        }
        set {
            $this->password = password_hash($value, PASSWORD_BCRYPT);
        }
    }
    
    #[ORM\Column]
    public DateTime $created_at {
        get {
            return $this->created_at;
        }
    }

    #[ORM\Column(nullable: true)]
    public ?DateTime $last_logged_in_at = null;

    #[ORM\Column]
    public int $failed_logins = 0;

    #[ORM\Column(nullable: true)]
    public ?DateTime $last_failed_login_at = null;

    public bool $is_locked {
        get {
            return $this->failed_logins >= 5;
        }
    }

    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
        $this->created_at = new DateTime();
    }

    public function checkLogin($password): bool
    {
        if ($this->last_failed_login_at?->add(DateInterval::createFromDateString('30 minutes')) < new DateTime()) {
            $this->resetFailedLogins();
        }
        if ($this->is_locked) {
            return false;
        } else {
            if (password_verify($password, $this->password)) {
                $this->last_logged_in_at = new DateTime();
                $this->resetFailedLogins();
                return true;
            } else {
                $this->increaseFailedLogins();
                return false;
            };
        }
    }

    private function resetFailedLogins(): void
    {
        $this->failed_logins = 0;
        unset($this->last_failed_login_at);
    }

    private function increaseFailedLogins(): void
    {
        $this->failed_logins++;
        $this->last_failed_login_at = new DateTime();
    }
}