<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GroupOfCity
 *
 * @ORM\Table(name="group_of_cities")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GroupOfCitiesRepository")
 */
class GroupOfCities
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
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;
    
    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\City",inversedBy="groupsOfCities")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $cities;
    
    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\OriginalQuestion",mappedBy="groupsOfCities")
     */
    private $originalQuestions;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     */
    private $creationDate;
    
    
    public function __construct()
    {
        // Default values
        $this->setCreationDate(new \DateTime());
        $this->setCities(array());
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
     * Set name
     *
     * @param string $name
     *
     * @return GroupOfCities
     */
    public function setName($name)
    {
        $this->name = $name;
        
        return $this;
    }
    
    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    public function setCities($cities)
    {
        $this->cities = $cities;
        
        return $this;
    }
    
    public function getCities()
    {
        return $this->cities;
    }
    
    public function getOriginalQuestions()
    {
        return $this->originalQuestions;
    }

    /**
     * Set creationDate
     *
     * @param \DateTime $creationDate
     *
     * @return GroupOfCities
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }
}

