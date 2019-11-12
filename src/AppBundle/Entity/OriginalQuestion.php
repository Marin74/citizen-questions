<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OriginalQuestion
 *
 * @ORM\Table(name="original_question")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\OriginalQuestionRepository")
 */
class OriginalQuestion
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
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\City",inversedBy="originalQuestions")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $cities;

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
     * Set text
     *
     * @param string $text
     *
     * @return OriginalQuestion
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
     * @return OriginalQuestion
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
    
    public function setCities($cities)
    {
        $this->cities = $cities;
        
        return $this;
    }
    
    public function getCities()
    {
        return $this->cities;
    }
}

