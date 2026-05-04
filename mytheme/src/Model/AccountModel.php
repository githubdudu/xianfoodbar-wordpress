<?php

namespace App\Model;

use App\Entity\Account;
use App\ORM\Model;

/**
 * @method Account|null find($id, $lockMode = null, $lockVersion = null)
 * @method Account|null findOneBy(string|array $name, string|int|null $value = null,  ?array $orderBy = null)
 * @method Account[]    findAll(?array $orderBy = null)
 * @method Account[]    findBy(string|array $name, string|int|null $value = null,  ?array $orderBy = null)
 */
class AccountModel extends Model {

    public function __construct() {
        return parent::__construct(Account::class);
    }

    public function getAccount(string $login): ?Account {
        return $this->findOneBy('account', $login);
    }
}