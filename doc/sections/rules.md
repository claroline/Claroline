Rule system
============

Claroline provides a rule system that can be used to add rules on an entity.
A validator is here to validate the rule along with your entity.

[Badge system][2] uses this system as awarding rule validation.

Usage
-----

In order to have rules on your entity you have to do two things.


### Step1: Ceating a rule class which extends the provided [Rule][3] class:

```php
<?php

namespace Claroline\CoreBundle\Entity\Badge;

use Claroline\CoreBundle\Badge\Constraints\ResultConstraint;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Rule\Entity\Rule;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class BadgeRule
 *
 * @ORM\Table(name="claro_badge_rule")
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Badge\BadgeRuleRepository")
 */
class BadgeRule extends Rule
{
    /**
     * @var Badge[]
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Badge\Badge", inversedBy="badgeRules")
     * @ORM\JoinColumn(name="badge_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $badge;

    /**
     * @param \Claroline\CoreBundle\Entity\Badge\Badge $badge
     *
     * @return BadgeRule
     */
    public function setBadge($badge)
    {
        $this->badge = $badge;

        return $this;
    }

    /**
     * @return \Claroline\CoreBundle\Entity\Badge\Badge[]|\Doctrine\Common\Collections\ArrayCollection
     */
    public function getBadge()
    {
        return $this->badge;
    }
}
```

### Step2: Make your class you want to add rules rulable by extending the [Rulable][4] provided class:

```php
<?php

namespace Claroline\CoreBundle\Entity\Badge;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Rule\Rulable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Claroline\CoreBundle\Form\Badge\Constraints as BadgeAssert;

/**
 * Class Badge
 *
 * @ORM\Table(name="claro_badge")
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Badge\BadgeRepository")
 * @ORM\HasLifecycleCallbacks
 * @BadgeAssert\AutomaticWithRules
 * @BadgeAssert\HasImage
 * @BadgeAssert\AtLeastOneTranslation
 */
class Badge extends Rulable
{
    /**
     * @var ArrayCollection|BadgeRule[]
     *
     * @ORM\OneToMany(targetEntity="Claroline\CoreBundle\Entity\Badge\BadgeRule", mappedBy="badge", cascade={"persist"})
     */
    protected $badgeRules;
}
```

Of course don't forget to add the rules on your entity.

Once you are done with that you can start adding rules on your entity.


Rule datas
----------

Here is the datas the rules can be fill in:
* **occurrence** : Number of times the action should occure
* **action** : Action that as to be executed (unique string key coming from an event, see [event-tracking][5] for more informations)
* **result** (optionnal) : Result of the action
* **resultComparison** (optionnal) : Comparison type for checking result of the action (>, >=, <, <= or =, please use [Rule class constants][6])
* **resource** (optionnal) : Resource on which the action was made

Rules validation
----------------

After adding rules on your entity it's now time to validate them.
And to validate rules you have to use the validator (quite logic isn't it?).

Here is the way to use the validator:

```php
<?php

use Claroline\CoreBundle\Rule\Validator;

$badgeRuleValidator = new Validator($this->getDoctrine()->getRepository('ClarolineCoreBundle:Log\Log'));
$validateLogs       = $badgeRuleValidator->validate($badge, $user);
```

The validate method return `false` if the rules aren't respected on the entity, and the list of logs that match the rules otherwise.



[index documentation][1]

[1]: ../index.md
[2]: badges.md
[3]: ../../../Rule/Entity/Rule.php
[4]: ../../../Rule/Rulable.php
[5]: event-tracking.php
[6]: ../../../Rule/Entity/Rule.php#L25
