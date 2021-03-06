<?php

namespace PROCERGS\OAuthBundle\Entity;

use PROCERGS\LoginCidadao\CoreBundle\Entity\Authorization;
use FOS\OAuthServerBundle\Entity\Client as BaseClient;
use Doctrine\ORM\Mapping as ORM;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Notification;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Client extends BaseClient
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $name;

    /**
     * @ORM\Column(type="text", nullable=false)
     */
    protected $description;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $maxNotificationLevel;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $landingPageURL;

    /**
     * @ORM\Column(type="text", nullable=false)
     */
    protected $termsOfUseURL;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    protected $allowedScopes;

    /**
     * @ORM\OneToMany(targetEntity="PROCERGS\LoginCidadao\CoreBundle\Entity\Authorization", mappedBy="client", cascade={"remove"}, orphanRemoval=true)
     */
    protected $authorizations;

    /**
     * @ORM\Column(type="string")
     */
    protected $siteUrl;

    /**
     * @ORM\OneToMany(targetEntity="PROCERGS\LoginCidadao\CoreBundle\Entity\Notification", mappedBy="client")
     */
    protected $notifications;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $picturePath;
    protected $tempPicturePath;

    /**
     * @Assert\File(maxSize="6000000")
     */
    protected $pictureFile;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $published;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $visible;

    public function __construct()
    {
        parent::__construct();
        $this->authorizations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->maxNotificationLevel = Notification::LEVEL_NORMAL;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setSiteUrl($url)
    {
        $this->siteUrl = $url;
    }

    public function getSiteUrl()
    {
        return $this->siteUrl;
    }

    public function getAuthorizations()
    {
        return $this->authorizations;
    }

    public function removeAuthorization(Authorization $authorization)
    {
        if ($this->authorizations->contains($authorization)) {
            $this->authorizations->removeElement($authorization);
        }
    }

    public function getMaxNotificationLevel()
    {
        return $this->maxNotificationLevel;
    }

    public function setMaxNotificationLevel($maxNotificationLevel)
    {
        $this->maxNotificationLevel = $maxNotificationLevel;

        return $this;
    }

    public function getLandingPageURL()
    {
        return $this->landingPageURL;
    }

    public function setLandingPageURL($landingPageURL)
    {
        $this->landingPageURL = $landingPageURL;
        return $this;
    }

    public function getTermsOfUseURL()
    {
        return $this->termsOfUseURL;
    }

    public function setTermsOfUseURL($termsOfUseURL)
    {
        $this->termsOfUseURL = $termsOfUseURL;
        return $this;
    }

    public function getAllowedScopes()
    {
        return $this->allowedScopes;
    }

    public function setAllowedScopes(array $allowedScopes)
    {
        $this->allowedScopes = $allowedScopes;

        return $this;
    }

    public function getAbsolutePicturePath()
    {
        return null === $this->picturePath ? null : $this->getPictureUploadRootDir() . DIRECTORY_SEPARATOR . $this->picturePath;
    }

    public function getPictureWebPath()
    {
        return null === $this->picturePath ? null : $this->getPictureUploadDir() . '/' . $this->picturePath;
    }

    protected function getPictureUploadRootDir()
    {
        return __DIR__ . '/../../../../web/' . $this->getPictureUploadDir();
    }

    protected function getPictureUploadDir()
    {
        return 'uploads/client-pictures';
    }

    public function setPictureFile(UploadedFile $pictureFile = null)
    {
        $this->pictureFile = $pictureFile;
        if (isset($this->picturePath)) {
            $this->tempPicturePath = $this->picturePath;
            $this->picturePath = null;
        } else {
            $this->picturePath = null;
        }
    }

    /**
     *
     * @return UploadedFile
     */
    public function getPictureFile()
    {
        return $this->pictureFile;
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function uploadPicture()
    {
        if (null === $this->getPictureFile()) {
            return;
        }

        $this->getPictureFile()->move(
                $this->getPictureUploadRootDir(), $this->picturePath
        );

        if (isset($this->tempPicturePath) && $this->tempPicturePath != $this->picturePath) {
            @unlink($this->getPictureUploadRootDir() . DIRECTORY_SEPARATOR . $this->tempPicturePath);
            $this->tempPicturePath = null;
        }

        $this->pictureFile = null;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (null !== $this->getPictureFile()) {
            $filename = sha1($this->getId());
            $this->picturePath = "$filename." . $this->getPictureFile()->guessExtension();
        }
    }

    /**
     * @ORM\PostRemove()
     */
    public function removePicturePostRemoval()
    {
        if ($file = $this->getAbsolutePicturePath()) {
            unlink($file);
        }
    }

    public function isVisible()
    {
        return $this->visible;
    }

    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }

    public function isPublished()
    {
        return $this->published;
    }

    public function setPublished($published)
    {
        $this->published = $published;

        return $this;
    }

}
