<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * QuestionCategory
 *
 * @ORM\Table(name="question_category")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\QuestionCategoryRepository")
 */
class QuestionCategory
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
     * @ORM\Column(name="code", type="string", length=255, unique=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;
    
    /**
     * @var string
     *
     * @ORM\Column(name="position", type="integer", unique=true)
     */
    private $position;
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Question",mappedBy="category")
     */
    private $questions;
    
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
        $this->setQuestions(array());
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
     * Set code
     *
     * @param string $code
     *
     * @return QuestionCategory
     */
    public function setCode($code)
    {
        $this->code = $code;
        
        return $this;
    }
    
    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return QuestionCategory
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
    
    public function setPosition($position)
    {
        $this->position = $position;
        
        return $this;
    }
    
    public function getPosition()
    {
        return $this->position;
    }
    
    public function setQuestions($questions)
    {
        $this->questions = $questions;
        
        return $this;
    }
    
    public function getQuestions()
    {
        return $this->questions;
    }
    
    /**
     * Set creationDate
     *
     * @param \DateTime $creationDate
     *
     * @return QuestionCategory
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

