<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 7/7/14
 * Time: 11:39 AM.
 */

namespace Icap\WebsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(name="icap__website_page")
 * @ORM\Entity(repositoryClass="Icap\WebsiteBundle\Repository\WebsitePageRepository")
 * @JMS\ExclusionPolicy("none")
 */
class WebsitePage
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $visible = true;

    /**
     * @ORM\Column(type="datetime", name="creation_date")
     * @Gedmo\Timestampable(on="create")
     * @JMS\Exclude
     */
    protected $creationDate;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(type="string")
     */
    protected $title;

    /**
     * @var text
     *
     * @ORM\Column(type="text", nullable=true)
     * @JMS\SerializedName("richText")
     */
    protected $richText;

    /**
     * @var text
     *
     * @ORM\Column(type="smallint", nullable=true)
     * @JMS\SerializedName("target")
     * @JMS\Accessor(getter="getTarget")
     */
    protected $target;

    /**
     * @var string
     * @Assert\Url()
     * @ORM\Column(type="string", nullable=true)
     */
    protected $url;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     * @JMS\SerializedName("isSection")
     */
    protected $isSection = false;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     */
    protected $type;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     * @ORM\JoinColumn(name="resource_node_id", referencedColumnName="id", nullable=true)
     * @JMS\Type("integer")
     * @JMS\Accessor(getter="getResourceNodeId")
     * @JMS\SerializedName("resourceNode")
     */
    protected $resourceNode;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @JMS\SerializedName("resourceNodeType")
     */
    protected $resourceNodeType;

    /**
     * @ORM\ManyToOne(targetEntity="Icap\WebsiteBundle\Entity\Website")
     * @ORM\JoinColumn(name="website_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * @JMS\Exclude
     */
    protected $website;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     * @JMS\Exclude
     */
    protected $left;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     * @JMS\Exclude
     */
    protected $level;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     * @JMS\Exclude
     */
    protected $right;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Icap\WebsiteBundle\Entity\WebsitePage")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     * @JMS\Type("integer")
     * @JMS\Accessor(getter="getParentId")
     */
    protected $parent;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @JMS\Type("boolean")
     */
    protected $isHomepage = false;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * @param mixed $visible
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;
    }

    /**
     * @return mixed
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param mixed $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return \Icap\WebsiteBundle\Entity\text
     */
    public function getRichText()
    {
        return $this->richText;
    }

    /**
     * @param \Icap\WebsiteBundle\Entity\text $richText
     */
    public function setRichText($richText)
    {
        $this->richText = $richText;
    }

    /**
     * @return int
     */
    public function getTarget()
    {
        if ($this->target === null) {
            return 0;
        }

        return $this->target;
    }

    /**
     * @param int $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return bool
     */
    public function getIsSection()
    {
        return $this->isSection;
    }

    /**
     * @param bool $isSection
     */
    public function setIsSection($isSection)
    {
        $this->isSection = $isSection;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $pageType = new WebsitePageTypeEnum($type);
        $this->type = $pageType->getValue();
    }

    /**
     * @return mixed
     */
    public function getResourceNode()
    {
        return $this->resourceNode;
    }

    /**
     * @return mixed
     */
    public function getResourceNodeId()
    {
        if ($this->resourceNode !== null) {
            return $this->resourceNode->getId();
        } else {
            return;
        }
    }

    /**
     * @param mixed $resourceNode
     */
    public function setResourceNode($resourceNode)
    {
        $this->resourceNode = $resourceNode;
    }

    /**
     * @return mixed
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @param mixed $website
     */
    public function setWebsite($website)
    {
        $this->website = $website;
    }

    /**
     * @return mixed
     */
    public function getLeft()
    {
        return $this->left;
    }

    /**
     * @param mixed $left
     */
    public function setLeft($left)
    {
        $this->left = $left;
    }

    /**
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param mixed $level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * @return mixed
     */
    public function getRight()
    {
        return $this->right;
    }

    /**
     * @param mixed $right
     */
    public function setRight($right)
    {
        $this->right = $right;
    }

    /**
     * @return mixed
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @param mixed $root
     */
    public function setRoot($root)
    {
        $this->root = $root;
    }

    /**
     * @return WebsitePage
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return mixed
     */
    public function getParentId()
    {
        return $this->parent->getId();
    }

    /**
     * @param WebsitePage $parent
     */
    public function setParent(WebsitePage $parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return string
     */
    public function getResourceNodeType()
    {
        return $this->resourceNodeType;
    }

    /**
     * @param string $resourceNodeType
     */
    public function setResourceNodeType($resourceNodeType)
    {
        $this->resourceNodeType = $resourceNodeType;
    }

    /**
     * @return mixed
     */
    public function getIsHomepage()
    {
        if ($this->isHomepage === null) {
            return false;
        } else {
            return $this->isHomepage;
        }
    }

    /**
     * @param mixed $isHomepage
     */
    public function setIsHomepage($isHomepage)
    {
        $this->isHomepage = $isHomepage;
    }

    public function isRoot()
    {
        return $this->level === 0;
    }

    public function exportToArray(RouterInterface $router = null, &$files = null)
    {
        $tmpFilePath = sys_get_temp_dir().DIRECTORY_SEPARATOR;

        $pageArray = [
            'id' => $this->id,
            'parent_id' => ($this->parent !== null) ? $this->parent->getId() : null,
            'is_root' => $this->level === 0,
            'visible' => $this->visible,
            'creation_date' => $this->creationDate,
            'title' => $this->title,
            'is_section' => $this->isSection,
            'description' => $this->description,
            'type' => $this->type,
            'is_homepage' => $this->getIsHomepage(),
            'url' => $this->url,
        ];

        //export
        if (isset($files) && $files !== null) {
            if ($this->type === WebsitePageTypeEnum::RESOURCE_PAGE) {
                $pageArray['resource_node_id'] = $this->resourceNode->getId();
                //also set URL as fallback if refereced resource is not found during import
                $pageArray['url'] = $router->generate('claro_resource_open', [
                    'resourceType' => $this->resourceNodeType,
                    'node' => $this->resourceNode->getId(),
                ], true);
            } elseif ($this->type === WebsitePageTypeEnum::BLANK_PAGE) {
                $richTextUid = uniqid('ws_page_content_').'.txt';
                file_put_contents($tmpFilePath.$richTextUid, $this->richText);
                $files[$richTextUid] = $tmpFilePath.$richTextUid;
                $pageArray['rich_text_path'] = $richTextUid;
            }
        //copy
        } else {
            $pageArray['resource_node'] = $this->resourceNode;
            $pageArray['resource_node_type'] = $this->resourceNodeType;
            $pageArray['rich_text'] = $this->richText;
        }

        return $pageArray;
    }

    public function importFromArray(array $optionsArray, $rootPath = null)
    {
        $this->visible = $optionsArray['visible'];
        $this->type = $optionsArray['type'];
        $this->creationDate = $optionsArray['creation_date'];
        $this->title = $optionsArray['title'];
        $this->isSection = $optionsArray['is_section'];
        $this->description = $optionsArray['description'];
        $this->isHomepage = $optionsArray['is_homepage'];
        if ($this->type === WebsitePageTypeEnum::BLANK_PAGE) {
            if (isset($optionsArray['rich_text'])) {
                $this->richText = $optionsArray['rich_text'];
            } elseif (isset($optionsArray['rich_text_path'])) {
                $this->richText = file_get_contents(
                    $rootPath.DIRECTORY_SEPARATOR.$optionsArray['rich_text_path']
                );
            }
        } elseif ($this->type === WebsitePageTypeEnum::RESOURCE_PAGE) {
            if (isset($optionsArray['resource_node'])) {
                $this->resourceNode = $optionsArray['resource_node'];
                $this->resourceNodeType = $this->resourceNode->getResourceType()->getName();
            } else {
                //fallback to url if resource not found
                $this->url = $optionsArray['url'];
                $this->type = WebsitePageTypeEnum::URL_PAGE;
            }
        } elseif ($this->type === WebsitePageTypeEnum::URL_PAGE) {
            $this->url = $optionsArray['url'];
            $this->target = 1;
        }
    }

    public function __toString()
    {
        return $this->getTitle();
    }
}
