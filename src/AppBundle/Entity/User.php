<?php

namespace AppBundle\Entity;

use FOS\UserBundle\Model\User as FosUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity
 */
class User extends FosUser
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    const ROLE_USER = "ROLE_USER";
    
    
    public function __construct()
    {
        parent::__construct();
    }
}

