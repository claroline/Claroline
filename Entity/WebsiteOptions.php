<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 7/7/14
 * Time: 10:07 AM
 */

namespace Icap\WebsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="icap__website_options")
 */
class WebsiteOptions implements JsonSerializable{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="Icap\WebsiteBundle\Entity\Website", inversedBy="options")
     * @ORM\JoinColumn(name="website_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected  $website;

    /** General Options */

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    protected $copyrightEnabled = false;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $copyrightText;

    /**
     * @var string
     *
     * @Assert\Choice(choices = {"google", "xiti"}, message = "Choose a valid provider.")
     * @ORM\Column(type="string", nullable=true)
     */
    protected $analyticsProvider;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $analyticsAccountId;

    /**
     * @var text
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $cssCode;


    /** Background Options */

    /**
     * @var string
     *
     * @Assert\Length(max = 11)
     * @ORM\Column(type="string", nullable=true)
     */
    protected $bgColor;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $bgImage;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $bgRepeat;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $bgPosition;

    /**
     * @var string
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $totalWidth;

    /** Banner Options */

    /**
     * @var string
     *
     * @Assert\Length(max = 11)
     * @ORM\Column(type="string", nullable=true)
     */
    protected $bannerBgColor;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $bannerBgImage;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $bannerBgRepeat;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $bannerBgPosition;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $bannerHeight;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    protected $bannerEnabled = true;

    /**
     * @var text
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $bannerText;

    /** Footer Options */

    /**
     * @var string
     *
     * @Assert\Length(max = 11)
     * @ORM\Column(type="string", nullable=true)
     */
    protected $footerBgColor;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $footerBgImage;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $footerBgRepeat;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $footerBgPosition;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $footerHeight;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    protected $footerEnabled = true;

    /**
     * @var text
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $footerText;


    /** Menu Options */

    /**
     * @var string
     *
     * @Assert\Length(max = 11)
     * @ORM\Column(type="string", nullable=true)
     */
    protected $menuBgColor;

    /**
     * @var string
     *
     * @Assert\Length(max = 11)
     * @ORM\Column(type="string", nullable=true)
     */
    protected $sectionBgColor;

    /**
     * @var string
     *
     * @Assert\Length(max = 11)
     * @ORM\Column(type="string", nullable=true)
     */
    protected $menuBorderColor;

    /**
     * @var string
     *
     * @Assert\Length(max = 7)
     * @ORM\Column(type="string", nullable=true)
     */
    protected $menuFontColor;

    /**
     * @var string
     *
     * @Assert\Length(max = 11)
     * @ORM\Column(type="string", nullable=true)
     */
    protected $sectionFontColor;

    /**
     * @var string
     *
     * @Assert\Length(max = 11)
     * @ORM\Column(type="string", nullable=true)
     */
    protected $menuHoverColor;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $menuFontFamily;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $menuFontStyle;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $menuFontSize = 12;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $menuFontWeight;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $menuWidth = 0;

    /**
     * @var string
     *
     * @Assert\Choice(choices = {"vertical", "horizontal"}, message = "Choose a valid menu orientation.")
     * @ORM\Column(type="string", nullable=true)
     */
    protected $menuOrientation;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return boolean
     */
    public function getCopyrightEnabled()
    {
        return $this->copyrightEnabled;
    }

    /**
     * @param boolean $copyrightEnabled
     */
    public function setCopyrightEnabled($copyrightEnabled)
    {
        $this->copyrightEnabled = $copyrightEnabled;
    }

    /**
     * @return string
     */
    public function getCopyrightText()
    {
        return $this->copyrightText;
    }

    /**
     * @param string $copyrightText
     */
    public function setCopyrightText($copyrightText)
    {
        $this->copyrightText = $copyrightText;
    }

    /**
     * @return string
     */
    public function getAnalyticsProvider()
    {
        return $this->analyticsProvider;
    }

    /**
     * @param string $analyticsProvider
     */
    public function setAnalyticsProvider($analyticsProvider)
    {
        $this->analyticsProvider = $analyticsProvider;
    }

    /**
     * @return string
     */
    public function getAnalyticsAccountId()
    {
        return $this->analyticsAccountId;
    }

    /**
     * @param string $analyticsAccountId
     */
    public function setAnalyticsAccountId($analyticsAccountId)
    {
        $this->analyticsAccountId = $analyticsAccountId;
    }

    /**
     * @return \Icap\WebsiteBundle\Entity\text
     */
    public function getCssCode()
    {
        return $this->cssCode;
    }

    /**
     * @param \Icap\WebsiteBundle\Entity\text $cssCode
     */
    public function setCssCode($cssCode)
    {
        $this->cssCode = $cssCode;
    }

    /**
     * @return string
     */
    public function getBgColor()
    {
        return $this->bgColor;
    }

    /**
     * @param string $bgColor
     */
    public function setBgColor($bgColor)
    {
        $this->bgColor = $bgColor;
    }

    /**
     * @return string
     */
    public function getBgImage()
    {
        return $this->bgImage;
    }

    /**
     * @param string $bgImage
     */
    public function setBgImage($bgImage)
    {
        $this->bgImage = $bgImage;
    }

    /**
     * @return string
     */
    public function getBgRepeat()
    {
        return $this->bgRepeat;
    }

    /**
     * @param string $bgRepeat
     */
    public function setBgRepeat($bgRepeat)
    {
        $this->bgRepeat = $bgRepeat;
    }

    /**
     * @return string
     */
    public function getBgPosition()
    {
        return $this->bgPosition;
    }

    /**
     * @param string $bgPosition
     */
    public function setBgPosition($bgPosition)
    {
        $this->bgPosition = $bgPosition;
    }

    /**
     * @return string
     */
    public function getBannerBgColor()
    {
        return $this->bannerBgColor;
    }

    /**
     * @param string $bannerBgColor
     */
    public function setBannerBgColor($bannerBgColor)
    {
        $this->bannerBgColor = $bannerBgColor;
    }

    /**
     * @return string
     */
    public function getBannerBgImage()
    {
        return $this->bannerBgImage;
    }

    /**
     * @param string $bannerBgImage
     */
    public function setBannerBgImage($bannerBgImage)
    {
        $this->bannerBgImage = $bannerBgImage;
    }

    /**
     * @return string
     */
    public function getBannerBgRepeat()
    {
        return $this->bannerBgRepeat;
    }

    /**
     * @param string $bannerBgRepeat
     */
    public function setBannerBgRepeat($bannerBgRepeat)
    {
        $this->bannerBgRepeat = $bannerBgRepeat;
    }

    /**
     * @return string
     */
    public function getBannerBgPosition()
    {
        return $this->bannerBgPosition;
    }

    /**
     * @param string $bannerBgPosition
     */
    public function setBannerBgPosition($bannerBgPosition)
    {
        $this->bannerBgPosition = $bannerBgPosition;
    }

    /**
     * @return int
     */
    public function getBannerHeight()
    {
        return $this->bannerHeight;
    }

    /**
     * @param int $bannerHeight
     */
    public function setBannerHeight($bannerHeight)
    {
        $this->bannerHeight = $bannerHeight;
    }

    /**
     * @return boolean
     */
    public function getBannerEnabled()
    {
        return $this->bannerEnabled;
    }

    /**
     * @param boolean $bannerEnabled
     */
    public function setBannerEnabled($bannerEnabled)
    {
        $this->bannerEnabled = $bannerEnabled;
    }

    /**
     * @return \Icap\WebsiteBundle\Entity\text
     */
    public function getBannerText()
    {
        return $this->bannerText;
    }

    /**
     * @param \Icap\WebsiteBundle\Entity\text $bannerText
     */
    public function setBannerText($bannerText)
    {
        $this->bannerText = $bannerText;
    }

    /**
     * @return string
     */
    public function getFooterBgColor()
    {
        return $this->footerBgColor;
    }

    /**
     * @param string $footerBgColor
     */
    public function setFooterBgColor($footerBgColor)
    {
        $this->footerBgColor = $footerBgColor;
    }

    /**
     * @return string
     */
    public function getFooterBgImage()
    {
        return $this->footerBgImage;
    }

    /**
     * @param string $footerBgImage
     */
    public function setFooterBgImage($footerBgImage)
    {
        $this->footerBgImage = $footerBgImage;
    }

    /**
     * @return string
     */
    public function getFooterBgRepeat()
    {
        return $this->footerBgRepeat;
    }

    /**
     * @param string $footerBgRepeat
     */
    public function setFooterBgRepeat($footerBgRepeat)
    {
        $this->footerBgRepeat = $footerBgRepeat;
    }

    /**
     * @return string
     */
    public function getFooterBgPosition()
    {
        return $this->footerBgPosition;
    }

    /**
     * @param string $footerBgPosition
     */
    public function setFooterBgPosition($footerBgPosition)
    {
        $this->footerBgPosition = $footerBgPosition;
    }

    /**
     * @return int
     */
    public function getFooterHeight()
    {
        return $this->footerHeight;
    }

    /**
     * @param int $footerHeight
     */
    public function setFooterHeight($footerHeight)
    {
        $this->footerHeight = $footerHeight;
    }

    /**
     * @return boolean
     */
    public function getFooterEnabled()
    {
        return $this->footerEnabled;
    }

    /**
     * @param boolean $footerEnabled
     */
    public function setFooterEnabled($footerEnabled)
    {
        $this->footerEnabled = $footerEnabled;
    }

    /**
     * @return \Icap\WebsiteBundle\Entity\text
     */
    public function getFooterText()
    {
        return $this->footerText;
    }

    /**
     * @param \Icap\WebsiteBundle\Entity\text $footerText
     */
    public function setFooterText($footerText)
    {
        $this->footerText = $footerText;
    }

    /**
     * @return string
     */
    public function getMenuBgColor()
    {
        return $this->menuBgColor;
    }

    /**
     * @param string $menuBgColor
     */
    public function setMenuBgColor($menuBgColor)
    {
        $this->menuBgColor = $menuBgColor;
    }

    /**
     * @return string
     */
    public function getSectionBgColor()
    {
        return $this->sectionBgColor;
    }

    /**
     * @param string $sectionBgColor
     */
    public function setSectionBgColor($sectionBgColor)
    {
        $this->sectionBgColor = $sectionBgColor;
    }

    /**
     * @return string
     */
    public function getMenuBorderColor()
    {
        return $this->menuBorderColor;
    }

    /**
     * @param string $menuBorderColor
     */
    public function setMenuBorderColor($menuBorderColor)
    {
        $this->menuBorderColor = $menuBorderColor;
    }

    /**
     * @return string
     */
    public function getMenuFontColor()
    {
        return $this->menuFontColor;
    }

    /**
     * @param string $menuFontColor
     */
    public function setMenuFontColor($menuFontColor)
    {
        $this->menuFontColor = $menuFontColor;
    }

    /**
     * @return string
     */
    public function getMenuHoverColor()
    {
        return $this->menuHoverColor;
    }

    /**
     * @param string $menuHoverColor
     */
    public function setMenuHoverColor($menuHoverColor)
    {
        $this->menuHoverColor = $menuHoverColor;
    }

    /**
     * @return string
     */
    public function getMenuFontFamily()
    {
        return $this->menuFontFamily;
    }

    /**
     * @param string $menuFontFamily
     */
    public function setMenuFontFamily($menuFontFamily)
    {
        $this->menuFontFamily = $menuFontFamily;
    }

    /**
     * @return string
     */
    public function getMenuFontStyle()
    {
        return $this->menuFontStyle;
    }

    /**
     * @param string $menuFontStyle
     */
    public function setMenuFontStyle($menuFontStyle)
    {
        $this->menuFontStyle = $menuFontStyle;
    }

    /**
     * @return string
     */
    public function getMenuFontWeight()
    {
        return $this->menuFontWeight;
    }

    /**
     * @param string $menuFontWeight
     */
    public function setMenuFontWeight($menuFontWeight)
    {
        $this->menuFontWeight = $menuFontWeight;
    }

    /**
     * @return int
     */
    public function getMenuFontSize()
    {
        return $this->menuFontSize;
    }

    /**
     * @param int $menuFontSize
     */
    public function setMenuFontSize($menuFontSize)
    {
        $this->menuFontSize = $menuFontSize;
    }

    /**
     * @return int
     */
    public function getMenuWidth()
    {
        return $this->menuWidth;
    }

    /**
     * @param int $menuWidth
     */
    public function setMenuWidth($menuWidth)
    {
        $this->menuWidth = $menuWidth;
    }

    /**
     * @return string
     */
    public function getMenuOrientation()
    {
        return $this->menuOrientation;
    }

    /**
     * @param string $menuOrientation
     */
    public function setMenuOrientation($menuOrientation)
    {
        $this->menuOrientation = $menuOrientation;
    }

    /**
     * @return string
     */
    public function getTotalWidth()
    {
        if ($this->totalWidth === null) {
            return 0;
        }

        return $this->totalWidth;
    }

    /**
     * @param string $totalWidth
     */
    public function setTotalWidth($totalWidth)
    {
        $this->totalWidth = $totalWidth;
    }

    /**
     * @return string
     */
    public function getSectionFontColor()
    {
        return $this->sectionFontColor;
    }

    /**
     * @param string $sectionFontColor
     */
    public function setSectionFontColor($sectionFontColor)
    {
        $this->sectionFontColor = $sectionFontColor;
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
     * @param $imgStr
     * @return null || string
     */
    public function getAbsolutePath($imgStr)
    {
        if ($this->$imgStr === null || filter_var($this->$imgStr, FILTER_VALIDATE_URL)) {
            return $this->$imgStr;
        } else {
            return $this->getUploadRootDir() . DIRECTORY_SEPARATOR . $this->$imgStr;
        }
    }

    /**
     * @param $imgStr
     * @return null || string
     */
    public function getWebPath($imgStr)
    {
        if ($this->$imgStr === null || filter_var($this->$imgStr, FILTER_VALIDATE_URL)) {
            return $this->$imgStr;
        } else {
            return DIRECTORY_SEPARATOR . $this->getUploadDir() . DIRECTORY_SEPARATOR . $this->$imgStr;
        }
    }

    /**
     * @throws \Exception
     * @return string
     */
    public function getUploadRootDir()
    {
        $ds = DIRECTORY_SEPARATOR;

        $uploadRootDir = sprintf(
            '%s%s..%s..%s..%s..%s..%s..%sweb%s%s',
            __DIR__, $ds, $ds, $ds, $ds, $ds, $ds, $ds, $ds, $this->getUploadDir()
        );
        $realpathUploadRootDir = realpath($uploadRootDir);

        if (false === $realpathUploadRootDir) {
            throw new \Exception(
                sprintf(
                    "Invalid upload root dir '%s'for uploading website images.",
                    $uploadRootDir
                )
            );
        }

        return $realpathUploadRootDir;
    }

    /**
     * @return string
     */
    public function getUploadDir()
    {
        return sprintf("uploads%swebsites%s".$this->getWebsite()->getId(), DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR);
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        return array(
            'copyrightEnabled'=> $this->getCopyrightEnabled(),
            'copyrightText'=> $this->getCopyrightText(),
            'analyticsProvider'=> $this->getAnalyticsProvider(),
            'analyticsAccountId'=> $this->getAnalyticsAccountId(),
            'cssCode'=> $this->getCssCode(),
            'bgColor'=> $this->getBgColor(),
            'bgImage'=> $this->getWebPath('bgImage'),
            'bgRepeat'=> $this->getBgRepeat(),
            'bgPosition'=> $this->getBgPosition(),
            'bannerBgColor'=> $this->getBannerBgColor(),
            'bannerBgImage'=> $this->getWebPath('bannerBgImage'),
            'bannerBgRepeat'=> $this->getBannerBgRepeat(),
            'bannerBgPosition'=> $this->getBgPosition(),
            'bannerHeight'=> $this->getBannerHeight(),
            'bannerEnabled'=> $this->getBannerEnabled(),
            'bannerText'=> $this->getBannerText(),
            'footerBgColor'=> $this->getFooterBgColor(),
            'footerBgImage'=> $this->getWebPath('footerBgImage'),
            'footerBgRepeat'=> $this->getFooterBgRepeat(),
            'footerBgPosition'=> $this->getFooterBgPosition(),
            'footerHeight'=> $this->getFooterHeight(),
            'footerEnabled'=> $this->getFooterEnabled(),
            'footerText'=> $this->getFooterText(),
            'menuBgColor'=> $this->getMenuBgColor(),
            'sectionBgColor'=> $this->getSectionBgColor(),
            'menuBorderColor'=> $this->getMenuBorderColor(),
            'menuFontColor'=> $this->getMenuFontColor(),
            'menuHoverColor'=> $this->getMenuHoverColor(),
            'menuFontFamily'=> $this->getMenuFontFamily(),
            'menuFontStyle'=> $this->getMenuFontStyle(),
            'menuFontWeight'=> $this->getMenuFontWeight(),
            'menuFontSize'=> $this->getMenuFontSize(),
            'menuWidth'=> $this->getMenuWidth(),
            'menuOrientation'=> $this->getMenuOrientation(),
            'totalWidth' => $this->getTotalWidth(),
            'sectionFontColor' => $this->getSectionFontColor()
        );
    }
}