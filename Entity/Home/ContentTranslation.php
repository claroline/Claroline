<?php
namespace Claroline\CoreBundle\Entity\Home;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;

/**
 * @ORM\Table(name="claro_content_translation", indexes={
 *      @ORM\Index(name="content_translation_idx", columns={"locale", "object_class", "field", "foreign_key"})
 * })
 * @ORM\Entity(repositoryClass="Gedmo\Translatable\Entity\Repository\TranslationRepository")
 */
class ContentTranslation extends AbstractTranslation
{

}