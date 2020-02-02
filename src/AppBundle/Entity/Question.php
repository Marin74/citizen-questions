<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Question
 *
 * @ORM\Table(name="question")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\QuestionRepository")
 */
class Question
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
     * @ORM\Column(name="text", type="text")
     */
    private $text;
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Answer",mappedBy="question")
     */
    private $answers;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\City",inversedBy="questions")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $city;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\GroupOfCities",inversedBy="questions")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $groupOfCities;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\QuestionCategory",inversedBy="questions")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $category;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     */
    private $creationDate;


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
     * Set text
     *
     * @param string $text
     *
     * @return Question
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set creationDate
     *
     * @param \DateTime $creationDate
     *
     * @return Question
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
    
    public function getAnswers()
    {
        return $this->answers;
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
    
    public function setGroupOfCities($groupOfCities)
    {
        $this->groupOfCities = $groupOfCities;
        
        return $this;
    }
    
    public function getGroupOfCities()
    {
        return $this->groupOfCities;
    }
    
    public function setCategory($category)
    {
        $this->category = $category;
        
        return $this;
    }
    
    public function getCategory()
    {
        return $this->category;
    }
    
    public function concernsThisCity(City $city)
    {
        if($this->getCity() == null && $this->getGroupOfCities() == null) {
            return true;
        }
        
        if($this->getCity() != null && $this->getCity()->getId() == $city->getId()) {
            return true;
        }
        
        if($this->getGroupOfCities() != null) {
            
            foreach($this->getGroupOfCities()->getCities() as $tempCity) {
                
                if($city->getId() == $tempCity->getId()) {
                    return true;
                }
            }
        }
        
        return false;
    }
}

