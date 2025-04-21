<?php

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'modules')]
class Module
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private ?int $id = null {
        get {
            return $this->id;
        }
    }

    #[ORM\Column]
    private string $name {
        get {
            return $this->name;
        }
        set {
            $this->name = $value;
        }
    }

    #[ORM\Column]
    private string $application {
        get {
            return $this->application;
        }
        set {
            $this->application = $value;
        }
    }

    #[ORM\Column]
    private string $file {
        get {
            return $this->file;
        }
        set {
            $this->file = $value;
        }
    }

    #[ORM\Column]
    private bool $description {
        get {
            return $this->description;
        }
        set {
            $this->description = $value;
        }
    }

    #[ORM\Column]
    private int $position {
        get {
            return $this->position;
        }
        set {
            $this->position = $value;
        }
    }
    
    #[ORM\Column]
    private bool $manage {
        get {
            return $this->description;
        }
        set {
            $this->description = $value;
        }
    }
}