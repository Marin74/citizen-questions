<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NotificationEmail
 *
 * @ORM\Table(name="notification_email")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\NotificationEmailRepository")
 */
class NotificationEmail
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\City",inversedBy="notificationEmails")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $city;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     */
    private $creationDate;
    
    
    
    public function __construct()
    {
        $this->setCreationDate(new \Datetime());
    }


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return NotificationEmail
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }
    
    public function setCity($city)
    {
        $this->city = $city;
        
        return $this;
    }
    
    public function getCity()
    {
        return $this->city;
    }
    
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
        
        return $this;
    }
    
    public function getCreationDate()
    {
        return $this->creationDate;
    }
}

