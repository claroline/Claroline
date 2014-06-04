<?php

namespace Claroline\CoreBundle\Entity;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="claro_user_public_profile_preferences")
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\UserPublicProfilePreferencesRepository")
 */
class UserPublicProfilePreferences
{
    const SHARE_POLICY_NOBODY              = 0;
    const SHARE_POLICY_NOBODY_LABEL        = 'make_public_profile_visible_to_nobody';
    const SHARE_POLICY_PLATFORM_USER       = 1;
    const SHARE_POLICY_PLATFORM_USER_LABEL = 'make_public_profile_visible_to_platform_user';
    const SHARE_POLICY_EVERYBODY           = 2;
    const SHARE_POLICY_EVERYBODY_LABEL     = 'make_public_profile_visible_to_everybody';

    /**
     * @var integer $id
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var User
     *
     * @ORM\OneToOne(targetEntity="User", inversedBy="publicProfilePreferences", cascade={"persist"})
     */
    protected $user;

    /**
     * @var bool
     *
     * @ORM\Column(type="integer", name="share_policy")
     */
    protected $sharePolicy = 0;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="display_phone_number")
     */
    protected $displayPhoneNumber = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="display_email")
     */
    protected $displayEmail = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="allow_mail_sending")
     */
    protected $allowMailSending = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="allow_message_sending")
     */
    protected $allowMessageSending = false;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return UserPublicProfilePreferences
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return \Claroline\CoreBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return UserPublicProfilePreferences
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getDisplayEmail()
    {
        return $this->displayEmail;
    }

    /**
     * @param boolean $displayEmail
     *
     * @return UserPublicProfilePreferences
     */
    public function setDisplayEmail($displayEmail)
    {
        $this->displayEmail = $displayEmail;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getDisplayPhoneNumber()
    {
        return $this->displayPhoneNumber;
    }

    /**
     * @param boolean $displayPhoneNumber
     *
     * @return UserPublicProfilePreferences
     */
    public function setDisplayPhoneNumber($displayPhoneNumber)
    {
        $this->displayPhoneNumber = $displayPhoneNumber;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getSharePolicy()
    {
        return $this->sharePolicy;
    }

    /**
     * @param boolean $sharePolicy
     *
     * @return UserPublicProfilePreferences
     */
    public function setSharePolicy($sharePolicy)
    {
        $this->sharePolicy = $sharePolicy;

        return $this;
    }

    public function isShared()
    {
        return $this->sharePolicy !== self::SHARE_POLICY_NOBODY;
    }

    /**
     * @return array
     */
    public static function getSharePolicies()
    {
        return array(
            self::SHARE_POLICY_NOBODY        => self::SHARE_POLICY_NOBODY_LABEL,
            self::SHARE_POLICY_PLATFORM_USER => self::SHARE_POLICY_PLATFORM_USER_LABEL,
            self::SHARE_POLICY_EVERYBODY     => self::SHARE_POLICY_EVERYBODY_LABEL
        );
    }

    /**
     * @param boolean $allowMailSending
     *
     * @return UserPublicProfilePreferences
     */
    public function setAllowMailSending($allowMailSending)
    {
        $this->allowMailSending = $allowMailSending;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getAllowMailSending()
    {
        return $this->allowMailSending;
    }

    /**
     * @param boolean $allowMessageSending
     *
     * @return UserPublicProfilePreferences
     */
    public function setAllowMessageSending($allowMessageSending)
    {
        $this->allowMessageSending = $allowMessageSending;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getAllowMessageSending()
    {
        return $this->allowMessageSending;
    }
}
