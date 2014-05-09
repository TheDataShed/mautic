<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
namespace Mautic\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mautic\CoreBundle\Helper\InputHelper;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Role
 * @ORM\Table(name="roles")
 * @ORM\Entity(repositoryClass="Mautic\UserBundle\Entity\RoleRepository")
 * @ORM\HasLifecycleCallbacks
 * @Serializer\ExclusionPolicy("all")
 */
class Role
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"full", "limited"})
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"full", "limited"})
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"full", "limited"})
     */
    protected $description;

    /**
     * @ORM\Column(name="is_admin", type="boolean")
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"full"})
     */
    protected $isAdmin = false;

    /**
     * @ORM\Column(name="date_added", type="datetime")
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"full"})
     */
    protected $dateAdded;

    /**
     * @ORM\OneToMany(targetEntity="Permission", mappedBy="role", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $permissions;

    /**
     * @param ClassMetadata $metadata
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('name', new Assert\NotBlank(
            array('message' => 'mautic.user.role.name.notblank')
        ));
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Role
     */
    public function setName($name)
    {
        $this->name = InputHelper::clean($name);

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
     * Add permissions
     *
     * @param \Mautic\UserBundle\Entity\Permission $permissions
     * @return Role
     */
    public function addPermission(\Mautic\UserBundle\Entity\Permission $permissions)
    {
        $permissions->setRole($this);

        $this->permissions[] = $permissions;

        return $this;
    }

    /**
     * Remove permissions
     *
     * @param \Mautic\UserBundle\Entity\Permission $permissions
     */
    public function removePermission(\Mautic\UserBundle\Entity\Permission $permissions)
    {
        $this->permissions->removeElement($permissions);
    }

    /**
     * Get permissions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Role
     */
    public function setDescription($description)
    {
        $this->description = InputHelper::clean($description);

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set dateAdded
     *
     * @param \DateTime $dateAdded
     * @return Role
     */
    public function setDateAdded($dateAdded)
    {
        $this->dateAdded = $dateAdded;

        return $this;
    }

    /**
     * Get dateAdded
     *
     * @return \DateTime
     */
    public function getDateAdded()
    {
        return $this->dateAdded;
    }

    /**
     * Set isAdmin
     *
     * @param boolean $isAdmin
     * @return Role
     */
    public function setIsAdmin($isAdmin)
    {
        $this->isAdmin = $isAdmin;

        return $this;
    }

    /**
     * Get isAdmin
     *
     * @return boolean
     */
    public function getIsAdmin()
    {
        return $this->isAdmin;
    }

    /**
     * @return bool
     */
    public function isAdmin() {
        return $this->getIsAdmin();
    }

    /**
     * Sets the Date/Time for new entities
     *
     * @ORM\PrePersist
     */
    public function onPrePersistSetDateAdded()
    {
        if (!$this->getId()) {
            $this->setDateAdded(new \DateTime());
        }
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->permissions = new \Doctrine\Common\Collections\ArrayCollection();
    }

}
