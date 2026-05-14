<?php

namespace App\AdminModule\Authenticator;

use Nette;

/**
 * Authenticator for admin users, implementing Nette\Security\Authenticator.
 */
class AdminAuthenticator implements Nette\Security\Authenticator
{
    /** @var Nette\Database\Explorer */
    private Nette\Database\Explorer $database;

    /** @var Nette\Security\Passwords */
    private Nette\Security\Passwords $passwords;

    /**
     * Constructor for AdminAuthenticator.
     *
     * @param  Nette\Database\Explorer  $database Database service
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
     * @param string $user Username (email)
     * @param string $password Password
     * @return Nette\Security\Identity
     * @throws Nette\Security\AuthenticationException
     */
    public function authenticate(string $user, string $password): Nette\Security\IIdentity
    {
        $row = $this->database->table('users')->where('email', $user)->fetch();

        if (!$row) 
            throw new Nette\Security\AuthenticationException('Uživatel nenalezen.');
        
        if (!$this->passwords->verify($password, $row->password))
            throw new Nette\Security\AuthenticationException('Špatné heslo.');

        return new Nette\Security\SimpleIdentity($row->id, $row->role, ['email' => $row->email]);
    }
}

