<?php

namespace App\Entity;

use App\ORM\Entity;
use App\ORM\Mapping\Column;
use App\ORM\Mapping\Id;
use App\Repository\AccountRepository;
use App\ORM\Mapping\Table;
use DateTimeInterface;

#[Entity(), Table("res_account")]
class Account
{
    #[Id, Column('integer', 11)]
    private $id;

    #[Column('string', 50)]
    private $account;

    #[Column('string', 30)]
    private $username;

    #[Column('string', 200)]
    private $password;

    #[Column('string', 10)]
    private $salt;

    #[Column('string')]
    private $avatar;

    #[Column('string', 255)]
    private $email;

    #[Column('json')]
    private $auth_typee = [];

    #[Column('integer')]
    private $store_id;

    #[Column('datetime')]
    private $create_time;

    #[Column('datetime')]
    private $update_time;

    #[Column('string', 50)]
    private $last_ip;

    #[Column('datetime')]
    private $last_login_time;

    #[Column('integer')]
    private $error_count;

    #[Column('datetime')]
    private $last_error_time;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAccount(): ?string
    {
        return $this->account;
    }

    public function setAccount(string $account): self
    {
        $this->account = $account;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getSalt(): ?string
    {
        return $this->salt;
    }

    public function setSalt(string $salt): self
    {
        $this->salt = $salt;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getAuthTypee(): ?array
    {
        return $this->auth_typee;
    }

    public function setAuthTypee(array $auth_typee): self
    {
        $this->auth_typee = $auth_typee;

        return $this;
    }

    public function getStoreId(): ?int
    {
        return $this->store_id;
    }

    public function setStoreId(int $store_id): self
    {
        $this->store_id = $store_id;

        return $this;
    }

    public function getCreateTime(): ?\DateTimeInterface
    {
        return $this->create_time;
    }

    public function setCreateTime(\DateTimeInterface $create_time): self
    {
        $this->create_time = $create_time;

        return $this;
    }

    public function getUpdateTime(): string|null|DateTimeInterface
    {
        return $this->update_time;
    }

    public function setUpdateTime(mixed $update_time): self
    {
        $this->update_time = $update_time;

        return $this;
    }

    public function getLastIp(): ?string
    {
        return $this->last_ip;
    }

    public function setLastIp(string $last_ip): self
    {
        $this->last_ip = $last_ip;

        return $this;
    }

    public function getLastLoginTime(): string|null|DateTimeInterface
    {
        return $this->last_login_time;
    }

    public function setLastLoginTime(mixed $last_login_time): self
    {
        $this->last_login_time = $last_login_time;

        return $this;
    }

    public function getErrorCount(): ?int
    {
        return $this->error_count;
    }

    public function setErrorCount(int $error_count): self
    {
        $this->error_count = $error_count;

        return $this;
    }

    public function getLastErrorTime(): string|null|DateTimeInterface
    {
        return $this->last_error_time;
    }

    public function setLastErrorTime(mixed $last_error_time): self
    {
        $this->last_error_time = $last_error_time;

        return $this;
    }
}
