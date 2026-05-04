<?php

namespace App\Entity;

use App\ORM\Entity;
use App\ORM\Mapping\Column;
use App\ORM\Mapping\Id;
use App\ORM\Mapping\Table;

#[Entity()]
#[Table('res_temp_user')]
class TempUser
{
    #[Id]
    #[Column('integer', 11)]
    private ?int $id = null;

    private $key;

    #[Column('string')]
    private ?string $uuid = null;

    #[Column('integer', 1)]
    private int $is_use = 0;

    #[Column('datetime')]
    private string|int $last_login_time = "";

    public function getId()
    {
        return $this->id;
    }
    public function getUuid()
    {
        return $this->uuid;
    }
    public function getIsUse()
    {
        return $this->is_use;
    }
    public function getLastLoginTime()
    {
        return $this->last_login_time;
    }

    public function setUuid(string $uuid): TempUser {$this->uuid = $uuid; return $this;}
    public function setIsUse(int $is_use): TempUser {$this->is_use = $is_use; return $this;}
    public function setLastLoginTime(): TempUser {$this->last_login_time = time(); return $this;}
}
