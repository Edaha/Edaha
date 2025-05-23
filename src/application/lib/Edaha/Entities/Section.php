<?php
namespace Edaha\Entities;

use Edaha\Entities\Board;

use DateTime;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

class Section
{
    public string $name;
    public bool $is_hidden;
    public DateTime $created_at;
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