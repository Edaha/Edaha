<?php
namespace Edaha\Entities;
use Edaha\Entities\Post;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity]
#[ORM\Table(name: 'post_attachments')]
class PostAttachment
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    public ?int $id = null {
        get {
            return $this->id;
        }
    }

    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'attachments')]
    #[ORM\JoinColumn(name: 'post_id', referencedColumnName: 'id', nullable: false)]
    public Post $post {
        get {
            return $this->post;
        }
    }

    #[ORM\Column]
    public string $path {
        get {
            return $this->path;
        }
    }
        
    #[ORM\Column]
    public string $file_name {
        get {
            return $this->file_name;
        }
    }

    #[ORM\Column]
    public string $original_name {
        get {
            return $this->original_name;
        }
    }

    #[ORM\Column]
    public string $md5_hash {
        get {
            return $this->md5_hash;
        }
    }

    public SplFileInfo $file;

    public function __construct($post, $path)
    {
        $this->post = $post;

        $this->file = new SplFileInfo($path);
        $this->path = $this->file->getRealPath();
        $this->file_name =  $this->file->getFileName();
        $this->original_name = $this->file->getFileName();
        $this->md5_hash = md5_file($path);
    }
}