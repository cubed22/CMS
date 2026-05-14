<?php
namespace App\Model;

/**
 * Users model class for managing users in the CMS.
 */
class Users extends BaseModel
{
    /** @var string */
    protected static $tableMain = "users";  

    /** @var string */
    protected static $recordClass = UserRecord::class;

    /**
     * Find a user by email.
     * 
     * @param string $email Email
     * @return UserRecord|false
     */
    public function findByEmail( string $email ): UserRecord|false
    {
        $data = $this->getDatabase()->table("users")->where("username", $email)->fetch();

        if ($data)
            return new UserRecord($data);

        return false;
    }
}

/**
 * User record class representing a single user.
 */
class UserRecord extends BaseRecord
{
  /**
   * Check if the user has admin role.
   * 
   * @return bool
   */
  public function isAdmin(): bool
  {
    return $this->data()->role == "admin";
  }

  /**
   * Get the full name of the user.
   * 
   * @return string
   */
  public function getFullName(): string
  {
    return $this->data()->name . " " . $this->data()->surname;
  }
}
