<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;

/**
 * @ORM\Entity(repositoryClass="Claroline\ClacoFormBundle\Repository\ClacoFormRepository")
 * @ORM\Table(name="claro_clacoformbundle_claco_form")
 */
class ClacoForm extends AbstractResource
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_claco_form", "api_user_min"})
     * @SerializedName("id")
     */
    protected $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"api_claco_form", "api_user_min"})
     * @SerializedName("template")
     */
    protected $template;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\ClacoFormBundle\Entity\Field",
     *     mappedBy="clacoForm"
     * )
     * @Groups({"api_claco_form", "api_user_min"})
     * @SerializedName("fields")
     */
    protected $fields;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\ClacoFormBundle\Entity\Category",
     *     mappedBy="clacoForm"
     * )
     * @Groups({"api_claco_form", "api_user_min"})
     * @SerializedName("categories")
     */
    protected $categories;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\ClacoFormBundle\Entity\Keyword",
     *     mappedBy="clacoForm"
     * )
     * @Groups({"api_claco_form", "api_user_min"})
     * @SerializedName("keywords")
     */
    protected $keywords;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     * @Groups({"api_claco_form", "api_user_min"})
     * @SerializedName("details")
     */
    protected $details;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->fields = new ArrayCollection();
        $this->keywords = new ArrayCollection();
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }

    public function getFields()
    {
        return $this->fields->toArray();
    }

    public function getCategories()
    {
        return $this->categories->toArray();
    }

    public function getKeywords()
    {
        return $this->keywords->toArray();
    }

    public function getDetails()
    {
        return $this->details;
    }

    public function setDetails($details)
    {
        $this->details = $details;
    }

    public function getMaxEntries()
    {
        return !is_null($this->details) && isset($this->details['max_entries']) ? $this->details['max_entries'] : 0;
    }

    public function setMaxEntries($maxEntries)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['max_entries'] = $maxEntries;
    }

    public function isCreationEnabled()
    {
        return !is_null($this->details) && isset($this->details['creation_enabled']) ? $this->details['creation_enabled'] : true;
    }

    public function setCreationEnabled($creationEnabled)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['creation_enabled'] = $creationEnabled;
    }

    public function isEditionEnabled()
    {
        return !is_null($this->details) && isset($this->details['edition_enabled']) ? $this->details['edition_enabled'] : true;
    }

    public function setEditionEnabled($editionEnabled)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['edition_enabled'] = $editionEnabled;
    }

    public function isModerated()
    {
        return !is_null($this->details) && isset($this->details['moderated']) ? $this->details['moderated'] : false;
    }

    public function setModerated($moderated)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['moderated'] = $moderated;
    }

    public function getDefaultHome()
    {
        return !is_null($this->details) && isset($this->details['default_home']) ? $this->details['default_home'] : 'menu';
    }

    public function setDefaultHome($defaultHome)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['default_home'] = $defaultHome;
    }

    public function getDisplayNbEntries()
    {
        return !is_null($this->details) && isset($this->details['display_nb_entries']) ? $this->details['display_nb_entries'] : 'none';
    }

    public function setDisplayNbEntries($displayNbEntries)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['display_nb_entries'] = $displayNbEntries;
    }

    public function getMenuPosition()
    {
        return !is_null($this->details) && isset($this->details['menu_position']) ? $this->details['menu_position'] : 'down';
    }

    public function setMenuPosition($menuPosition)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['menu_position'] = $menuPosition;
    }

    public function isRandomEnabled()
    {
        return !is_null($this->details) && isset($this->details['random_enabled']) ? $this->details['random_enabled'] : false;
    }

    public function setRandomEnabled($randomEnabled)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['random_enabled'] = $randomEnabled;
    }

    public function getRandomCategories()
    {
        return !is_null($this->details) && isset($this->details['random_categories']) ? $this->details['random_categories'] : [];
    }

    public function setRandomCategories(array $categories)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['random_categories'] = $categories;
    }

    public function getRandomStartDate()
    {
        return !is_null($this->details) && isset($this->details['random_start_date']) ? $this->details['random_start_date'] : null;
    }

    public function setRandomStartDate(\DateTime $startDate = null)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['random_start_date'] = !is_null($startDate) ? $startDate->format('Y-m-d') : null;
    }

    public function getRandomEndDate()
    {
        return !is_null($this->details) && isset($this->details['random_end_date']) ? $this->details['random_end_date'] : null;
    }

    public function setRandomEndDate(\DateTime $endDate = null)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['random_end_date'] = !is_null($endDate) ? $endDate->format('Y-m-d') : null;
    }

    public function getSearchEnabled()
    {
        return !is_null($this->details) && isset($this->details['search_enabled']) ? $this->details['search_enabled'] : true;
    }

    public function setSearchEnabled($searchEnabled)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['search_enabled'] = $searchEnabled;
    }

    public function isSearchColumnEnabled()
    {
        return !is_null($this->details) && isset($this->details['search_column_enabled']) ? $this->details['search_column_enabled'] : true;
    }

    public function setSearchColumnEnabled($searchColumnEnabled)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['search_column_enabled'] = $searchColumnEnabled;
    }

    public function getSearchColumns()
    {
        return !is_null($this->details) && isset($this->details['search_columns']) ?
            $this->details['search_columns'] :
            ['title', 'creationDateString', 'userString', 'categoriesString', 'keywordsString', 'actions'];
    }

    public function setSearchColumns(array $searchColumns)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['search_columns'] = $searchColumns;
    }

    public function getDisplayMetadata()
    {
        return !is_null($this->details) && isset($this->details['display_metadata']) ? $this->details['display_metadata'] : 'none';
    }

    public function setDisplayMetadata($displayMetadata)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['display_metadata'] = $displayMetadata;
    }

    public function getLockedFieldsFor()
    {
        return !is_null($this->details) && isset($this->details['locked_fields_for']) ? $this->details['locked_fields_for'] : 'user';
    }

    public function setLockedFieldsFor($lockedFieldsFor)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['locked_fields_for'] = $lockedFieldsFor;
    }

    public function getDisplayCategories()
    {
        return !is_null($this->details) && isset($this->details['display_categories']) ? $this->details['display_categories'] : false;
    }

    public function setDisplayCategories($displayCategories)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['display_categories'] = $displayCategories;
    }

    public function getOpenCategories()
    {
        return !is_null($this->details) && isset($this->details['open_categories']) ? $this->details['open_categories'] : false;
    }

    public function setOpenCategories($openCategories)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['open_categories'] = $openCategories;
    }

    public function isCommentsEnabled()
    {
        return !is_null($this->details) && isset($this->details['comments_enabled']) ? $this->details['comments_enabled'] : false;
    }

    public function setCommentsEnabled($commentsEnabled)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['comments_enabled'] = $commentsEnabled;
    }

    public function isAnonymousCommentsEnabled()
    {
        return !is_null($this->details) && isset($this->details['anonymous_comments_enabled']) ? $this->details['anonymous_comments_enabled'] : false;
    }

    public function setAnonymousCommentsEnabled($anonymousCommentsEnabled)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['anonymous_comments_enabled'] = $anonymousCommentsEnabled;
    }

    public function getModerateComments()
    {
        return !is_null($this->details) && isset($this->details['moderate_comments']) ? $this->details['moderate_comments'] : 'none';
    }

    public function setModerateComments($moderateComments)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['moderate_comments'] = $moderateComments;
    }

    public function getDisplayComments()
    {
        return !is_null($this->details) && isset($this->details['display_comments']) ? $this->details['display_comments'] : false;
    }

    public function setDisplayComments($displayComments)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['display_comments'] = $displayComments;
    }

    public function getOpenComments()
    {
        return !is_null($this->details) && isset($this->details['open_comments']) ? $this->details['open_comments'] : false;
    }

    public function setOpenComments($openComments)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['open_comments'] = $openComments;
    }

    public function getDisplayCommentAuthor()
    {
        return !is_null($this->details) && isset($this->details['display_comment_author']) ? $this->details['display_comment_author'] : true;
    }

    public function setDisplayCommentAuthor($displayCommentAuthor)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['display_comment_author'] = $displayCommentAuthor;
    }

    public function getDisplayCommentDate()
    {
        return !is_null($this->details) && isset($this->details['display_comment_date']) ? $this->details['display_comment_date'] : true;
    }

    public function setDisplayCommentDate($displayCommentDate)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['display_comment_date'] = $displayCommentDate;
    }

    public function isVotesEnabled()
    {
        return !is_null($this->details) && isset($this->details['votes_enabled']) ? $this->details['votes_enabled'] : false;
    }

    public function setVotesEnabled($votesEnabled)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['votes_enabled'] = $votesEnabled;
    }

    public function getDisplayVotes()
    {
        return !is_null($this->details) && isset($this->details['display_votes']) ? $this->details['display_votes'] : false;
    }

    public function setDisplayVotes($displayVotes)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['display_votes'] = $displayVotes;
    }

    public function getOpenVotes()
    {
        return !is_null($this->details) && isset($this->details['open_votes']) ? $this->details['open_votes'] : false;
    }

    public function setOpenVotes($openVotes)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['open_votes'] = $openVotes;
    }

    public function getVotesStartDate()
    {
        return !is_null($this->details) && isset($this->details['votes_start_date']) ? $this->details['votes_start_date'] : null;
    }

    public function setVotesStartDate(\DateTime $startDate = null)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['votes_start_date'] = !is_null($startDate) ? $startDate->format('Y-m-d') : null;
    }

    public function getVotesEndDate()
    {
        return !is_null($this->details) && isset($this->details['votes_end_date']) ? $this->details['votes_end_date'] : null;
    }

    public function setVotesEndDate(\DateTime $endDate = null)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['votes_end_date'] = !is_null($endDate) ? $endDate->format('Y-m-d') : null;
    }

    public function isKeywordsEnabled()
    {
        return !is_null($this->details) && isset($this->details['keywords_enabled']) ? $this->details['keywords_enabled'] : false;
    }

    public function setKeywordsEnabled($keywordsEnabled)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['keywords_enabled'] = $keywordsEnabled;
    }

    public function isNewKeywordsEnabled()
    {
        return !is_null($this->details) && isset($this->details['new_keywords_enabled']) ? $this->details['new_keywords_enabled'] : false;
    }

    public function setNewKeywordsEnabled($newKeywordsEnabled)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['new_keywords_enabled'] = $newKeywordsEnabled;
    }

    public function getDisplayKeywords()
    {
        return !is_null($this->details) && isset($this->details['display_keywords']) ? $this->details['display_keywords'] : false;
    }

    public function setDisplayKeywords($displayKeywords)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['display_keywords'] = $displayKeywords;
    }

    public function getOpenKeywords()
    {
        return !is_null($this->details) && isset($this->details['open_keywords']) ? $this->details['open_keywords'] : false;
    }

    public function setOpenKeywords($openKeywords)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['open_keywords'] = $openKeywords;
    }

    public function getUseTemplate()
    {
        return !is_null($this->details) && isset($this->details['use_template']) ? $this->details['use_template'] : false;
    }

    public function setUseTemplate($useTemplate)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['use_template'] = $useTemplate;
    }

    public function getDefaultDisplayMode()
    {
        return !is_null($this->details) && isset($this->details['default_display_mode']) ? $this->details['default_display_mode'] : 'table';
    }

    public function setDefaultDisplayMode($defaultDisplayMode)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['default_display_mode'] = $defaultDisplayMode;
    }

    public function getDisplayTitle()
    {
        return !is_null($this->details) && isset($this->details['display_title']) ? $this->details['display_title'] : 'title';
    }

    public function setDisplayTitle($displayTitle)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['display_title'] = $displayTitle;
    }

    public function getDisplaySubtitle()
    {
        return !is_null($this->details) && isset($this->details['display_subtitle']) ? $this->details['display_subtitle'] : 'title';
    }

    public function setDisplaySubtitle($displaySubtitle)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['display_subtitle'] = $displaySubtitle;
    }

    public function getDisplayContent()
    {
        return !is_null($this->details) && isset($this->details['display_content']) ? $this->details['display_content'] : 'title';
    }

    public function setDisplayContent($displayContent)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['display_content'] = $displayContent;
    }
}
