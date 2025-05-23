<?php
namespace Edaha\Entities;

use Doctrine\ORM\Mapping as ORM;
use Edaha\Types\ModuleType;

#[ORM\Entity]
#[ORM\Table(name: 'modules')]
class Module
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
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

    #[ORM\Column]
    public ModuleType $type {
        get {
            return $this->type;
        }
        set {
            $this->type = $value;
        }
    }

    #[ORM\Column]
    public string $class {
        get {
            return $this->class;
        }
        set {
            if (!class_exists($value)) {
                throw new \InvalidArgumentException("Class $value does not exist.");
            }
            $this->class = $value;
        }
    }

    #[ORM\Column]
    public string $description {
        get {
            return $this->description;
        }
        set {
            $this->description = $value;
        }
    }
    
    #[ORM\Column]
    public bool $is_manage {
        get {
            return $this->is_manage;
        }
        set {
            $this->is_manage = $value;
        }
    }

    public function __construct(
        string $name,
        ModuleType $type,
        string $class,
        string $description,
        bool $is_manage
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->class = $class;
        $this->description = $description;
        $this->is_manage = $is_manage;
    }
}
