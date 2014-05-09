<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\UserBundle\Entity;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Criteria;
use FOS\OAuthServerBundle\Model\ClientInterface;
use Mautic\CoreBundle\Helper\InputHelper;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class User
 *
 * @package Mautic\UserBundle\Entity
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="Mautic\UserBundle\Entity\UserRepository")
 * @ORM\HasLifecycleCallbacks
 * @Serializer\ExclusionPolicy("all")
 */
class User implements AdvancedUserInterface, \Serializable
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"full", "limited"})
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=25, unique=true)
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"full"})
     */
    protected $username;

    /**
     * @ORM\Column(type="string", length=64)
     */
    protected $password;

    /**
     * Used for when updating the password
     * @var
     */
    protected $plainPassword;

    /**
     * Used for updating account
     * @var
     */
    protected $currentPassword;

    /**
     * @ORM\Column(name="first_name",type="string", length=50)
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"full", "limited"})
     */
    protected $firstName;

    /**
     * @ORM\Column(name="last_name", type="string", length=50)
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"full", "limited"})
     */
    protected $lastName;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"full"})
     */
    protected $email;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"full"})
     */
    protected $position;

    /**
     * @ORM\ManyToOne(targetEntity="Role")
     * @ORM\JoinColumn(name="role_id", referencedColumnName="id")
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"full", "limited"})
     */
    protected $role;

    /**
     * @ORM\Column(name="is_active", type="boolean")
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"full"})
     */
    protected $isActive = true;

    /**
     * @ORM\Column(name="date_added", type="datetime")
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"full"})
     */
    protected $dateAdded;

    /**
     * Stores active role permissions
     * @var
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"full"})
     */
    protected $activePermissions;

    /**
     * @param ClassMetadata $metadata
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('username', new Assert\NotBlank(
            array('message' => 'mautic.user.user.username.notblank')
        ));

        $metadata->addConstraint(new UniqueEntity(
            array(
                'fields'           => array('username'),
                'message'          => 'mautic.user.user.username.unique',
                'repositoryMethod' => 'checkUniqueUsernameEmail'
            )
        ));

        $metadata->addPropertyConstraint('firstName', new Assert\NotBlank(
            array('message' => 'mautic.user.user.firstname.notblank')
        ));

        $metadata->addPropertyConstraint('lastName',  new Assert\NotBlank(
            array('message' => 'mautic.user.user.lastname.notblank')
        ));

        $metadata->addPropertyConstraint('email',  new Assert\NotBlank(
            array('message' => 'mautic.user.user.email.valid')
        ));

        $metadata->addPropertyConstraint('email',     new Assert\Email(
            array(
                'message' => 'mautic.user.user.email.valid',
                'groups'  => array('SecondPass')
            )
        ));

        $metadata->addConstraint(new UniqueEntity(
            array(
                'fields'           => array('email'),
                'message'          => 'mautic.user.user.email.unique',
                'repositoryMethod' => 'checkUniqueUsernameEmail'
            )
        ));

        $metadata->addPropertyConstraint('role',  new Assert\NotBlank(
            array('message' => 'mautic.user.user.role.notblank')
        ));

        $metadata->addPropertyConstraint('plainPassword',  new Assert\NotBlank(
            array(
                'message' => 'mautic.user.user.password.notblank',
                'groups'  => array('CheckPassword')
            )
        ));

        $metadata->addPropertyConstraint('plainPassword',  new Assert\Length(
            array(
                'min'        => 6,
                'minMessage' => 'mautic.user.user.password.minlength',
                'groups'     => array('CheckPassword')
            )
        ));

        $metadata->addPropertyConstraint('currentPassword',  new SecurityAssert\UserPassword(
            array(
                'message' => 'mautic.user.account.password.userpassword',
                'groups'  => array('Profile')
            )
        ));

        $metadata->addPropertyConstraint('isActive',  new Assert\NotBlank(
            array('message' => 'mautic.user.user.isactive.notblank')
        ));

        $metadata->setGroupSequence(array('User', 'SecondPass', 'CheckPassword'));
    }

    /**
     * @param Form $form
     * @return array
     */
    static public function determineValidationGroups(Form $form) {
        $data = $form->getData();
        $groups = array('User', 'SecondPass');

        //check if creating a new user or editing an existing user and the password has been updated
        if (!$data->getId() || ($data->getId() && $data->getPlainPassword())) {
            $groups[] = 'CheckPassword';
        }

        //require current password if on profile page
        if ($form->has('currentPassword')) {
            $groups[] = 'Profile';
        }

        //check to see if
        return $groups;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return null|string
     */
    public function getSalt()
    {
        //bcrypt generates its own salt
        return null;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Get plain password
     *
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * Get current password (that a user has typed into a form)
     *
     * @return string
     */
    public function getCurrentPassword()
    {
        return $this->currentPassword;
    }

    /**
     * Determines user role for symfony authentication
     *
     * @return array
     */
    public function getRoles()
    {
        $roles = array(
            "ROLE_API",
            (($this->getRole()->isAdmin()) ? "ROLE_ADMIN" : "ROLE_USER")
        );
        return $roles;
    }

    public function eraseCredentials()
    {

    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            $this->role,
            $this->isActive
        ));
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            $this->role,
            $this->isActive
        ) = unserialize($serialized);
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
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = InputHelper::clean($username);

        return $this;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Set plain password
     *
     * @param $plainPassword
     * @return $this
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * Set current password
     *
     * @param $currentPassword
     * @return $this
     */
    public function setCurrentPassword($currentPassword)
    {
        $this->currentPassword = $currentPassword;

        return $this;
    }


    /**
     * Set firstName
     *
     * @param string $firstName
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = InputHelper::clean($firstName);

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = InputHelper::clean($lastName);

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Get full name
     *
     * @param bool $lastFirst
     * @return string
     */
    public function getName($lastFirst = false)
    {
        $fullName = ($lastFirst) ? $this->lastName . ", " . $this->firstName : $this->firstName . " " . $this->lastName;
        return $fullName;

    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = InputHelper::clean($email);

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return User
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set dateAdded
     *
     * @param \DateTime $dateAdded
     * @return User
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
     * @return bool
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->isActive;
    }

    /**
     * Set role
     *
     * @param \Mautic\UserBundle\Entity\Role $role
     * @return User
     */
    public function setRole(\Mautic\UserBundle\Entity\Role $role = null)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return \Mautic\UserBundle\Entity\Role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set active permissions
     *
     * @param array $permissions
     */
    public function setActivePermissions(array $permissions) {
        $this->activePermissions = $permissions;
        return $this;
    }

    /**
     * Get active permissions
     *
     * @return mixed
     */
    public function getActivePermissions() {
        return $this->activePermissions;
    }

    /**
     * Set position
     *
     * @param string $position
     * @return User
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Sets the Date/Time for new entities
     *
     * @ORM\PrePersist
     */
    public function onPrePersistSetDefaults()
    {
        if (!$this->getId()) {
            $this->setDateAdded(new \DateTime());
        }
        if ($this->getIsActive() === null) {
            $this->setIsActive(true);
        }
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->clients = new \Doctrine\Common\Collections\ArrayCollection();
    }

}
