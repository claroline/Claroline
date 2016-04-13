<?php

namespace Icap\BadgeBundle\Form\DataTransformer;

use Icap\BadgeBundle\Entity\Badge;
use Icap\BadgeBundle\Manager\BadgeManager;
use Doctrine\Common\Collections\Collection;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @DI\Service("icap_badge.transformer.badge_picker")
 */
class BadgePickerTransformer implements DataTransformerInterface
{
    /**
     * @var \Icap\BadgeBundle\Manager\BadgeManager
     */
    private $badgeManager;

    /**
     * @DI\InjectParams({
     *     "badgeManager" = @DI\Inject("icap_badge.manager.badge")
     * })
     */
    public function __construct(BadgeManager $badgeManager)
    {
        $this->badgeManager = $badgeManager;
    }

    /**
     * @param Badge[]|Badge $value
     *
     * @return int|string
     */
    public function transform($value)
    {
        if (is_array($value) || $value instanceof Collection) {
            $transformedData = array();

            foreach ($value as $entity) {
                $transformedData[] = array(
                    'id' => $entity->getId(),
                    'text' => $entity->getName(),
                    'icon' => $entity->getWebPath(),
                    'description' => $entity->getDescription(),
                );
            }

            return $transformedData;
        }

        if ($value instanceof Badge) {
            return array(
                'id' => $value->getId(),
                'text' => $value->getName(),
                'icon' => $value->getWebPath(),
                'description' => $value->getDescription(),
            );
        }

        return;
    }

    /**
     * @param int $badgeId
     *
     * @return Badge|null
     *
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function reverseTransform($badgeId)
    {
        if (!$badgeId) {
            return;
        }

        $badge = $this->badgeManager->getById($badgeId);

        if (null === $badge) {
            throw new TransformationFailedException();
        }

        return $badge;
    }
}
