<?php

namespace App\FrontendModule\Authenticator;

use Nette;

/**
 * Authenticator for frontend users, implementing Nette\Security\Authenticator.
 */
class FrontendAuthenticator implements Nette\Security\Authenticator
{
    /** @var Nette\Database\Context */
    private $database;

    /** @var Nette\Security\Passwords */
    private $passwords;

    /**
    * Constructor for FrontendAuthenticator.
    *
    * @param Nette\Database\Context $database Database service
    * @param Nette\Security\Passwords $passwords Passwords service
    */
    public function __construct(Nette\Database\Explorer $database, Nette\Security\Passwords $passwords)
    {
        $this->database = $database;
        $this->passwords = $passwords;
    }

    /**
    * Authenticate a user based on provided username and password.
    * 
    * @param string $username Username (email)
    * @param string $password Password
    * @return Nette\Security\Identity
    * @throws Nette\Security\AuthenticationException
    */
    public function authenticate(string $username, string $password): Nette\Security\IIdentity
    {
        $row = $this->database->table('customers')->where('username', $username)->fetch();

        if (!$row) {
            $rowAlias = $this->database->table('customer_alias')->where('username', $username)->fetch();
            if ($rowAlias) {
                if (!$this->passwords->verify($password, $rowAlias->password)) {
                    throw new Nette\Security\AuthenticationException('Nesprávné přihlašovací jméno nebo heslo.');
                }
                $row = $this->database->table('customers')->where('id', $rowAlias->user_id)->fetch();
            } else {
                throw new Nette\Security\AuthenticationException('Nesprávné přihlašovací jméno nebo heslo.');
            }
        } else {
            if (!$this->passwords->verify($password, $row->password)) {
                throw new Nette\Security\AuthenticationException('Nesprávné přihlašovací jméno nebo heslo.');
            }
        }
        
        if ($row->confirmed == 0) {
            throw new Nette\Security\AuthenticationException('Váš účet ještě nebyl potvrzen. Zkontrolujte svou e-mailovou schránku.');
        }

        return new Nette\Security\SimpleIdentity($row->id, 'customer', ['username' => $row->username]);
    }
}

