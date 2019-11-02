<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Answer
 *
 * @ORM\Table(name="answer")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AnswerRepository")
 */
class Answer
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
     * @ORM\Column(name="text", type="text", nullable=true)
     */
    private $text;

    /**
     * @var bool
     *
     * @ORM\Column(name="inProgram", type="boolean")
     */
    private $inProgram;

    /**
     * @var bool
     *
     * @ORM\Column(name="isPriority", type="boolean")
     */
    private $isPriority;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     */
    private $creationDate;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ElectoralList",inversedBy="answers")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $list;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Question",inversedBy="answers")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $question;


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
     * @return Answer
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
     * Set inProgram
     *
     * @param boolean $inProgram
     *
     * @return Answer
     */
    public function setInProgram($inProgram)
    {
        $this->inProgram = $inProgram;

        return $this;
    }

    /**
     * Get inProgram
     *
     * @return bool
     */
    public function getInProgram()
    {
        return $this->inProgram;
    }

    /**
     * Set isPriority
     *
     * @param boolean $isPriority
     *
     * @return Answer
     */
    public function setIsPriority($isPriority)
    {
        $this->isPriority = $isPriority;

        return $this;
    }

    /**
     * Get isPriority
     *
     * @return bool
     */
    public function getIsPriority()
    {
        return $this->isPriority;
    }

    /**
     * Set creationDate
     *
     * @param \DateTime $creationDate
     *
     * @return Answer
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
    
    public function setList($list)
    {
        $this->list = $list;
        
        return $this;
    }
    
    public function getList()
    {
        return $this->list;
    }
    
    public function setQuestion($question)
    {
        $this->question = $question;
        
        return $this;
    }
    
    public function getQuestion()
    {
        return $this->question;
    }
}

