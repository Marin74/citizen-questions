<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * City
 *
 * @ORM\Table(name="city")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CityRepository")
 */
class City
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="insee", type="string", length=255, unique=true)
     */
    private $insee;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     */
    private $creationDate;
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ElectoralList",mappedBy="city")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $lists;
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Question",mappedBy="city")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $questions;
    
    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\OriginalQuestion",mappedBy="cities")
     */
    private $originalQuestions;
    
    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\GroupOfCities",mappedBy="cities")
     */
    private $groupsOfCities;


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
     * @return City
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

    /**
     * Set insee
     *
     * @param string $insee
     *
     * @return City
     */
    public function setInsee($insee)
    {
        $this->insee = $insee;

        return $this;
    }

    /**
     * Get insee
     *
     * @return string
     */
    public function getInsee()
    {
        return $this->insee;
    }

    /**
     * Set creationDate
     *
     * @param \DateTime $creationDate
     *
     * @return City
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
    
    public function getLists()
    {
        return $this->lists;
    }
    
    public function getNameForUrl()
    {
        $name = "-";
        
        if(!empty($name)) {
            $name = strtolower($this->getName());
            
            $name = str_replace("’", "-", $name);
            $name = str_replace("'", "-", $name);
            $name = str_replace('"', "", $name);
            $name = str_replace("/", "", $name);
            $name = str_replace("|", "", $name);
            $name = str_replace('\\', "", $name);
            $name = str_replace('?', "", $name);
            $name = str_replace('!', "", $name);
            $name = str_replace('(', "", $name);
            $name = str_replace(')', "", $name);
            $name = str_replace('°', "", $name);
            $name = str_replace('&', "", $name);
            $name = str_replace('§', "", $name);
            $name = str_replace('*', "", $name);
            $name = str_replace('%', "", $name);
            $name = str_replace(':', "", $name);
            $name = str_replace(',', "", $name);
            $name = str_replace('=', "", $name);
            $name = str_replace('+', "", $name);
            $name = str_replace('%', "", $name);
            $name = str_replace('.', "", $name);
            $name = str_replace('<', "", $name);
            $name = str_replace('>', "", $name);
            $name = str_replace('@', "", $name);
            $name = str_replace('#', "", $name);
            
            $name = str_replace("é", "e", $name);
            $name = str_replace("è", "e", $name);
            $name = str_replace("ê", "e", $name);
            $name = str_replace("ë", "e", $name);
            $name = str_replace("â", "a", $name);
            $name = str_replace("à", "a", $name);
            $name = str_replace("ä", "a", $name);
            $name = str_replace("ô", "o", $name);
            $name = str_replace("ö", "o", $name);
            $name = str_replace("ù", "u", $name);
            $name = str_replace("ü", "u", $name);
            $name = str_replace("î", "i", $name);
            $name = str_replace("ï", "i", $name);
            $name = str_replace("ç", "c", $name);
            $name = str_replace("æ", "ae", $name);
            $name = str_replace("œ", "oe", $name);
            
            $name = trim($name);
            
            $name = str_replace(" ", "-", $name);
            
            while(strpos($name, "--") !== false) {
                
                $name = str_replace("--", "-", $name);
            }
        }
        
        return $name;
    }
    
    public function getValidatedLists()
    {
        $lists = array();
        
        foreach($this->getLists() as $list) {
            if($list->isValidated()) {
                $lists[] = $list;
            }
        }
        
        return $lists;
    }
    
    public function getQuestions()
    {
        return $this->questions;
    }
    
    public function getOriginalQuestions()
    {
        return $this->originalQuestions;
    }
    
    public function getGroupsOfCities()
    {
        return $this->groupsOfCities;
    }
}

