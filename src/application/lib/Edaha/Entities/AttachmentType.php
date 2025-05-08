<?php
namespace Edaha\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'attachment_types')]
class AttachmentType
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
    public string $extension {
        get {
            return $this->extension;
        }
        set {
            $this->extension = $value;
        }
    }

    #[ORM\Column]
    public string $mime_type {
        get {
            return $this->mime_type;
        }
        set {
            $this->mime_type = $value;
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
    public bool $is_image = false {
        get {
            return $this->is_image;
        }
        set {
            $this->is_image = $value;
        }
    }

    #[ORM\Column]
    public bool $is_video = false {
        get {
            return $this->is_video;
        }
        set {
            $this->is_video = $value;
        }
    }

    #[ORM\Column]
    public bool $is_audio = false {
        get {
            return $this->is_audio;
        }
        set {
            $this->is_audio = $value;
        }
    }
}