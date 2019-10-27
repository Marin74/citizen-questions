<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ElectoralList
 *
 * @ORM\Table(name="electoral_list")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ElectoralListRepository")
 */
class ElectoralList
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
     * @ORM\Column(name="fistnameHeadOfList", type="string", length=255)
     */
    private $fistnameHeadOfList;

    /**
     * @var string
     *
     * @ORM\Column(name="lastnameHeadOfList", type="string", length=255)
     */
    private $lastnameHeadOfList;

    /**
     * @var string
     *
     * @ORM\Column(name="website", type="string", length=255, nullable=true)
     */
    private $website;

    /**
     * @var string
     *
     * @ORM\Column(name="facebook", type="string", length=255, nullable=true)
     */
    private $facebook;

    /**
     * @var string
     *
     * @ORM\Column(name="supportedBy", type="string", length=255, nullable=true)
     */
    private $supportedBy;

    /**
     * @var string
     *
     * @ORM\Column(name="contactFirstname", type="string", length=255)
     */
    private $contactFirstname;

    /**
     * @var string
     *
     * @ORM\Column(name="contactLastname", type="string", length=255)
     */
    private $contactLastname;

    /**
     * @var string
     *
     * @ORM\Column(name="contactEmail", type="string", length=255)
     */
    private $contactEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="contactPhone", type="string", length=255)
     */
    private $contactPhone;

    /**
     * @var string
     *
     * @ORM\Column(name="contactRole", type="string", length=255)
     */
    private $contactRole;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=1)
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="validationDate", type="datetime", nullable=true)
     */
    private $validationDate;


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
     * @return ElectoralList
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
     * Set fistnameHeadOfList
     *
     * @param string $fistnameHeadOfList
     *
     * @return ElectoralList
     */
    public function setFistnameHeadOfList($fistnameHeadOfList)
    {
        $this->fistnameHeadOfList = $fistnameHeadOfList;

        return $this;
    }

    /**
     * Get fistnameHeadOfList
     *
     * @return string
     */
    public function getFistnameHeadOfList()
    {
        return $this->fistnameHeadOfList;
    }

    /**
     * Set lastnameHeadOfList
     *
     * @param string $lastnameHeadOfList
     *
     * @return ElectoralList
     */
    public function setLastnameHeadOfList($lastnameHeadOfList)
    {
        $this->lastnameHeadOfList = $lastnameHeadOfList;

        return $this;
    }

    /**
     * Get lastnameHeadOfList
     *
     * @return string
     */
    public function getLastnameHeadOfList()
    {
        return $this->lastnameHeadOfList;
    }

    /**
     * Set website
     *
     * @param string $website
     *
     * @return ElectoralList
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website
     *
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set facebook
     *
     * @param string $facebook
     *
     * @return ElectoralList
     */
    public function setFacebook($facebook)
    {
        $this->facebook = $facebook;

        return $this;
    }

    /**
     * Get facebook
     *
     * @return string
     */
    public function getFacebook()
    {
        return $this->facebook;
    }

    /**
     * Set supportedBy
     *
     * @param string $supportedBy
     *
     * @return ElectoralList
     */
    public function setSupportedBy($supportedBy)
    {
        $this->supportedBy = $supportedBy;

        return $this;
    }

    /**
     * Get supportedBy
     *
     * @return string
     */
    public function getSupportedBy()
    {
        return $this->supportedBy;
    }

    /**
     * Set contactFirstname
     *
     * @param string $contactFirstname
     *
     * @return ElectoralList
     */
    public function setContactFirstname($contactFirstname)
    {
        $this->contactFirstname = $contactFirstname;

        return $this;
    }

    /**
     * Get contactFirstname
     *
     * @return string
     */
    public function getContactFirstname()
    {
        return $this->contactFirstname;
    }

    /**
     * Set contactLastname
     *
     * @param string $contactLastname
     *
     * @return ElectoralList
     */
    public function setContactLastname($contactLastname)
    {
        $this->contactLastname = $contactLastname;

        return $this;
    }

    /**
     * Get contactLastname
     *
     * @return string
     */
    public function getContactLastname()
    {
        return $this->contactLastname;
    }

    /**
     * Set contactEmail
     *
     * @param string $contactEmail
     *
     * @return ElectoralList
     */
    public function setContactEmail($contactEmail)
    {
        $this->contactEmail = $contactEmail;

        return $this;
    }

    /**
     * Get contactEmail
     *
     * @return string
     */
    public function getContactEmail()
    {
        return $this->contactEmail;
    }

    /**
     * Set contactPhone
     *
     * @param string $contactPhone
     *
     * @return ElectoralList
     */
    public function setContactPhone($contactPhone)
    {
        $this->contactPhone = $contactPhone;

        return $this;
    }

    /**
     * Get contactPhone
     *
     * @return string
     */
    public function getContactPhone()
    {
        return $this->contactPhone;
    }

    /**
     * Set contactRole
     *
     * @param string $contactRole
     *
     * @return ElectoralList
     */
    public function setContactRole($contactRole)
    {
        $this->contactRole = $contactRole;

        return $this;
    }

    /**
     * Get contactRole
     *
     * @return string
     */
    public function getContactRole()
    {
        return $this->contactRole;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return ElectoralList
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set validationDate
     *
     * @param \DateTime $validationDate
     *
     * @return ElectoralList
     */
    public function setValidationDate($validationDate)
    {
        $this->validationDate = $validationDate;

        return $this;
    }

    /**
     * Get validationDate
     *
     * @return \DateTime
     */
    public function getValidationDate()
    {
        return $this->validationDate;
    }
}

