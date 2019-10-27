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
     * Set inProgram
     *
     * @param boolean $inProgram
     *
     * @return Question
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
     * @return Question
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
}

