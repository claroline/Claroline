<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 7/7/14
 * Time: 10:07 AM.
 */

namespace Icap\WebsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="icap__website_options")
 * @ORM\HasLifecycleCallbacks()
 * @JMS\ExclusionPolicy("none")
 */
class WebsiteOptions
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Exclude
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="Icap\WebsiteBundle\Entity\Website", inversedBy="options")
     * @ORM\JoinColumn(name="website_id", referencedColumnName="id", onDelete="CASCADE")
     * @JMS\Exclude
     */
    protected $website;

    /** General Options */

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     * @JMS\SerializedName("copyrightEnabled")
     */
    protected $copyrightEnabled = false;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @JMS\SerializedName("copyrightText")
     */
    protected $copyrightText = '';

    /**
     * @var string
     *
     * @Assert\Choice(choices = {"none", "google", "xiti"}, message = "Choose a valid provider.")
     * @ORM\Column(type="string", nullable=true)
     * @JMS\SerializedName("analyticsProvider")
     */
    protected $analyticsProvider = 'none';

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @JMS\SerializedName("analyticsAccountId")
     */
    protected $analyticsAccountId;

    /**
     * @var text
     *
     * @ORM\Column(type="text", nullable=true)
     * @JMS\SerializedName("cssCode")
     */
    protected $cssCode;

    /** Background Options */

    /**
     * @var string
     *
     * @Assert\Length(max = 11)
     * @ORM\Column(type="string", nullable=true)
     * @JMS\SerializedName("bgColor")
     */
    protected $bgColor = '#EFEFEF';

    /**
     * @var string
     *
     * @Assert\Length(max = 11)
     * @ORM\Column(type="string", nullable=true)
     * @JMS\SerializedName("bgContentColor")
     */
    protected $bgContentColor = '#FFFFFF';

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @JMS\Accessor(getter="getBgImageForWeb")
     * @JMS\SerializedName("bgImage")
     */
    protected $bgImage;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @JMS\SerializedName("bgRepeat")
     */
    protected $bgRepeat = 'no-repeat';

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @JMS\SerializedName("bgPosition")
     */
    protected $bgPosition = 'center center';

    /**
     * @var string
     *
     * @ORM\Column(type="integer", nullable=true)
     * @JMS\SerializedName("totalWidth")
     * @JMS\Accessor(getter="getTotalWidth")
     */
    protected $totalWidth = 960;

    /** Banner Options */

    /**
     * @var string
     *
     * @Assert\Length(max = 11)
     * @ORM\Column(type="string", nullable=true)
     * @JMS\SerializedName("bannerBgColor")
     */
    protected $bannerBgColor = '#1466B8';

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @JMS\Accessor(getter="getBannerBgImageForWeb")
     * @JMS\SerializedName("bannerBgImage")
     */
    protected $bannerBgImage;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @JMS\SerializedName("bannerBgRepeat")
     */
    protected $bannerBgRepeat = 'no-repeat';

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @JMS\SerializedName("bannerBgPosition")
     */
    protected $bannerBgPosition = 'center center';

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     * @JMS\SerializedName("bannerHeight")
     */
    protected $bannerHeight = 50;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     * @JMS\SerializedName("bannerEnabled")
     */
    protected $bannerEnabled = true;

    /**
     * @var text
     *
     * @ORM\Column(type="text", nullable=true)
     * @JMS\SerializedName("bannerText")
     */
    protected $bannerText;

    /** Footer Options */

    /**
     * @var string
     *
     * @Assert\Length(max = 11)
     * @ORM\Column(type="string", nullable=true)
     * @JMS\SerializedName("footerBgColor")
     */
    protected $footerBgColor = '#1466B8';

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @JMS\Accessor(getter="getFooterBgImageForWeb")
     * @JMS\SerializedName("footerBgImage")
     */
    protected $footerBgImage;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @JMS\SerializedName("footerBgRepeat")
     */
    protected $footerBgRepeat = 'no-repeat';

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @JMS\SerializedName("footerBgPosition")
     */
    protected $footerBgPosition = 'center center';

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     * @JMS\SerializedName("footerHeight")
     */
    protected $footerHeight = 50;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     * @JMS\SerializedName("footerEnabled")
     */
    protected $footerEnabled = true;

    /**
     * @var text
     *
     * @ORM\Column(type="text", nullable=true)
     * @JMS\SerializedName("footerText")
     */
    protected $footerText;

    /** Menu Options */

    /**
     * @var string
     *
     * @Assert\Length(max = 11)
     * @ORM\Column(type="string", nullable=true)
     * @JMS\SerializedName("menuBgColor")
     */
    protected $menuBgColor = '#E0E0E0';

    /**
     * @var string
     *
     * @Assert\Length(max = 11)
     * @ORM\Column(type="string", nullable=true)
     * @JMS\SerializedName("sectionBgColor")
     */
    protected $sectionBgColor = '#C7C7C7';

    /**
     * @var string
     *
     * @Assert\Length(max = 11)
     * @ORM\Column(type="string", nullable=true)
     * @JMS\SerializedName("menuBorderColor")
     */
    protected $menuBorderColor = '#C2C2C2';

    /**
     * @var string
     *
     * @Assert\Length(max = 7)
     * @ORM\Column(type="string", nullable=true)
     * @JMS\SerializedName("menuFontColor")
     */
    protected $menuFontColor = '#4A4A4A';

    /**
     * @var string
     *
     * @Assert\Length(max = 11)
     * @ORM\Column(type="string", nullable=true)
     * @JMS\SerializedName("sectionFontColor")
     */
    protected $sectionFontColor = '#4A4A4A';

    /**
     * @var string
     *
     * @Assert\Length(max = 11)
     * @ORM\Column(type="string", nullable=true)
     * @JMS\SerializedName("menuHoverColor")
     */
    protected $menuHoverColor = '#A3A3A3';

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @JMS\SerializedName("menuFontFamily")
     */
    protected $menuFontFamily = 'inherit';

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @JMS\SerializedName("menuFontStyle")
     */
    protected $menuFontStyle = 'normal';

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     * @JMS\SerializedName("menuFontSize")
     */
    protected $menuFontSize = 12;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @JMS\SerializedName("menuFontWeight")
     */
    protected $menuFontWeight = 'normal';

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     * @JMS\SerializedName("menuWidth")
     */
    protected $menuWidth = 150;

    /**
     * @var string
     *
     * @Assert\Choice(choices = {"vertical", "horizontal"}, message = "Choose a valid menu orientation.")
     * @ORM\Column(type="string", nullable=true)
     * @JMS\SerializedName("menuOrientation")
     */
    protected $menuOrientation = 'vertical';

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function getCopyrightEnabled()
    {
        return $this->copyrightEnabled;
    }

    /**
     * @param bool $copyrightEnabled
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
    public function getBgContentColor()
    {
        return $this->bgContentColor;
    }

    /**
     * @param string $bgContentColor
     */
    public function setBgContentColor($bgContentColor)
    {
        $this->bgContentColor = $bgContentColor;
    }

    /**
     * @return string
     */
    public function getBgImage()
    {
        return $this->bgImage;
    }

    /**
     * @return string
     */
    public function getBgImageForWeb()
    {
        return $this->getWebPath('bgImage');
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
     * @return string
     */
    public function getBannerBgImageForWeb()
    {
        return $this->getWebPath('bannerBgImage');
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
     * @return bool
     */
    public function getBannerEnabled()
    {
        return $this->bannerEnabled;
    }

    /**
     * @param bool $bannerEnabled
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
     * @return string
     */
    public function getFooterBgImageForWeb()
    {
        return $this->getWebPath('footerBgImage');
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
     * @return bool
     */
    public function getFooterEnabled()
    {
        return $this->footerEnabled;
    }

    /**
     * @param bool $footerEnabled
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
     * @return Website
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
     * @param $webDir
     *
     * @return string
     */
    protected function getAbsolutePath($webDir, $imgStr)
    {
        if ($this->$imgStr === null || filter_var($this->$imgStr, FILTER_VALIDATE_URL)) {
            return $this->$imgStr;
        } else {
            return $webDir.DIRECTORY_SEPARATOR.$this->getUploadDir().DIRECTORY_SEPARATOR.$this->$imgStr;
        }
    }

    /**
     * @param $imgStr
     *
     * @return string
     */
    public function getWebPath($imgStr)
    {
        if ($this->$imgStr === null || filter_var($this->$imgStr, FILTER_VALIDATE_URL)) {
            return $this->$imgStr;
        } else {
            return DIRECTORY_SEPARATOR.$this->getUploadDir().DIRECTORY_SEPARATOR.$this->$imgStr;
        }
    }

    /**
     * @return string
     */
    public function getUploadDir()
    {
        $uploadDir = sprintf('uploads%swebsites', DIRECTORY_SEPARATOR);
        if ($this->getWebsite()->isTest()) {
            return $uploadDir.DIRECTORY_SEPARATOR.'tests';
        }

        return $uploadDir.DIRECTORY_SEPARATOR.$this->getWebsite()->getId();
    }

    public function exportToArray($webDir, &$files = null)
    {
        $tmpFilePath = sys_get_temp_dir().DIRECTORY_SEPARATOR;
        $bgImageUid = $this->bgImage;
        $bannerBgImageUid = $this->bannerBgImage;
        $footerBgImageUid = $this->footerBgImage;

        $optionsArray = [
            'copyright_enabled' => $this->copyrightEnabled,
            'copyright_text' => $this->copyrightText,
            'analytics_provider' => $this->analyticsProvider,
            'analytics_account_id' => $this->analyticsAccountId,
            'bg_color' => $this->bgColor,
            'bg_image' => $bgImageUid,
            'bg_repeat' => $this->bgRepeat,
            'bg_position' => $this->bgPosition,
            'total_width' => $this->totalWidth,
            'banner_bg_color' => $this->bannerBgColor,
            'banner_bg_image' => $bannerBgImageUid,
            'banner_bg_repeat' => $this->bannerBgRepeat,
            'banner_bg_position' => $this->bannerBgPosition,
            'banner_height' => $this->bannerHeight,
            'banner_enabled' => $this->bannerEnabled,
            'footer_bg_color' => $this->footerBgColor,
            'footer_bg_image' => $footerBgImageUid,
            'footer_bg_repeat' => $this->footerBgRepeat,
            'footer_bg_position' => $this->footerBgPosition,
            'footer_height' => $this->footerHeight,
            'footer_enabled' => $this->footerEnabled,
            'menu_bg_color' => $this->menuBgColor,
            'section_bg_color' => $this->sectionBgColor,
            'menu_border_color' => $this->menuBorderColor,
            'menu_font_color' => $this->menuFontColor,
            'section_font_color' => $this->sectionFontColor,
            'menu_hover_color' => $this->menuHoverColor,
            'menu_font_family' => $this->menuFontFamily,
            'menu_font_style' => $this->menuFontStyle,
            'menu_font_size' => $this->menuFontSize,
            'menu_font_weight' => $this->menuFontWeight,
            'menu_width' => $this->menuWidth,
            'menu_orientation' => $this->menuOrientation,
        ];

        if (isset($files) && $files !== null) {
            //Create bgImage file
            if ($bgImageUid !== null && !filter_var($bgImageUid, FILTER_VALIDATE_URL)) {
                copy($this->getAbsolutePath($webDir, 'bgImage'), $tmpFilePath.$bgImageUid);
                $files[$bgImageUid] = $tmpFilePath.$bgImageUid;
            }
            //Create bannerBgImage file
            if ($bannerBgImageUid !== null && !filter_var($bannerBgImageUid, FILTER_VALIDATE_URL)) {
                copy($this->getAbsolutePath($webDir, 'bannerBgImage'), $tmpFilePath.$bannerBgImageUid);
                $files[$bannerBgImageUid] = $tmpFilePath.$bannerBgImageUid;
            }
            //Create footerBgImage file
            if ($footerBgImageUid !== null && !filter_var($footerBgImageUid, FILTER_VALIDATE_URL)) {
                copy($this->getAbsolutePath($webDir, 'footerBgImage'), $tmpFilePath.$footerBgImageUid);
                $files[$footerBgImageUid] = $tmpFilePath.$footerBgImageUid;
            }

            //Create file for csscode
            $cssCodeUid = null;
            if ($this->cssCode !== null && !empty($this->cssCode)) {
                $cssCodeUid = uniqid('ws_css_').'.txt';
                file_put_contents($tmpFilePath.$cssCodeUid, $this->cssCode);
                $files[$cssCodeUid] = $tmpFilePath.$cssCodeUid;
            }
            //Create file for banner text
            $bannerTextUid = null;
            if ($this->bannerText !== null && !empty($this->bannerText)) {
                $bannerTextUid = uniqid('ws_banner_').'.txt';
                file_put_contents($tmpFilePath.$bannerTextUid, $this->bannerText);
                $files[$bannerTextUid] = $tmpFilePath.$bannerTextUid;
            }
            //Create file for footer text
            $footerTextUid = null;
            if ($this->footerText !== null && !empty($this->footerText)) {
                $footerTextUid = uniqid('ws_footer_').'.txt';
                file_put_contents($tmpFilePath.$footerTextUid, $this->footerText);
                $files[$footerTextUid] = $tmpFilePath.$footerTextUid;
            }
            $optionsArray['css_code_path'] = $cssCodeUid;
            $optionsArray['banner_text_path'] = $bannerTextUid;
            $optionsArray['footer_text_path'] = $footerTextUid;
        } else {
            $optionsArray['css_code'] = $this->cssCode;
            $optionsArray['banner_text'] = $this->bannerText;
            $optionsArray['footer_text'] = $this->footerText;
        }

        return $optionsArray;
    }

    public function createUploadFolder($uploadFolderPath)
    {
        if (!file_exists($uploadFolderPath)) {
            mkdir($uploadFolderPath, 0777, true);
        }
    }

    public function importFromArray($webDir, array $optionsArray, $rootPath = null)
    {
        $uploadedDir = $webDir.DIRECTORY_SEPARATOR.$this->getUploadDir();
        $this->createUploadFolder($uploadedDir);
        $this->copyrightEnabled = $optionsArray['copyright_enabled'];
        $this->copyrightText = $optionsArray['copyright_text'];
        $this->analyticsProvider = $optionsArray['analytics_provider'];
        $this->analyticsAccountId = $optionsArray['analytics_account_id'];
        //Get content for css code
        $cssCode = null;
        if (isset($optionsArray['css_code_path']) && $optionsArray['css_code_path'] !== null) {
            $cssCode = file_get_contents(
                $rootPath.DIRECTORY_SEPARATOR.$optionsArray['css_code_path']
            );
        } elseif (isset($optionsArray['css_code'])) {
            $cssCode = $optionsArray['css_code'];
        }
        $this->cssCode = $cssCode;
        $this->bgColor = $optionsArray['bg_color'];
        $this->bgImage = $optionsArray['bg_image'];
        //Copy bg image to web folder
        if ($this->bgImage !== null && !filter_var($this->bgImage, FILTER_VALIDATE_URL)) {
            copy(
                $rootPath.DIRECTORY_SEPARATOR.$this->bgImage,
                $uploadedDir.DIRECTORY_SEPARATOR.$this->bgImage
            );
        }
        $this->bgRepeat = $optionsArray['bg_repeat'];
        $this->bgPosition = $optionsArray['bg_position'];
        $this->totalWidth = $optionsArray['total_width'];
        $this->bannerBgColor = $optionsArray['banner_bg_color'];
        $this->bannerBgImage = $optionsArray['banner_bg_image'];
        //Copy banner bg image to web folder
        if ($this->bannerBgImage !== null && !filter_var($this->bannerBgImage, FILTER_VALIDATE_URL)) {
            copy(
                $rootPath.DIRECTORY_SEPARATOR.$this->bannerBgImage,
                $uploadedDir.DIRECTORY_SEPARATOR.$this->bannerBgImage
            );
        }
        $this->bannerBgRepeat = $optionsArray['banner_bg_repeat'];
        $this->bannerBgPosition = $optionsArray['banner_bg_position'];
        $this->bannerHeight = $optionsArray['banner_height'];
        $this->bannerEnabled = $optionsArray['banner_enabled'];
        //Get content for banner text
        $bannerText = null;
        if (isset($optionsArray['banner_text_path']) && $optionsArray['banner_text_path'] !== null) {
            $bannerText = file_get_contents(
                $rootPath.DIRECTORY_SEPARATOR.$optionsArray['banner_text_path']
            );
        } elseif (isset($optionsArray['banner_text'])) {
            $bannerText = $optionsArray['banner_text'];
        }
        $this->bannerText = $bannerText;
        $this->footerBgColor = $optionsArray['footer_bg_color'];
        $this->footerBgImage = $optionsArray['footer_bg_image'];
        //Copy footer bg image to web folder
        if ($this->footerBgImage !== null && !filter_var($this->footerBgImage, FILTER_VALIDATE_URL)) {
            copy(
                $rootPath.DIRECTORY_SEPARATOR.$this->footerBgImage,
                $uploadedDir.DIRECTORY_SEPARATOR.$this->footerBgImage
            );
        }
        $this->footerBgRepeat = $optionsArray['footer_bg_repeat'];
        $this->footerBgPosition = $optionsArray['footer_bg_position'];
        $this->footerHeight = $optionsArray['footer_height'];
        $this->footerEnabled = $optionsArray['footer_enabled'];
        //Get content for footer text
        $footerText = null;
        if (isset($optionsArray['footer_text_path']) && $optionsArray['footer_text_path'] !== null) {
            $footerText = file_get_contents(
                $rootPath.DIRECTORY_SEPARATOR.$optionsArray['footer_text_path']
            );
        } elseif (isset($optionsArray['footer_text'])) {
            $footerText = $optionsArray['footer_text'];
        }
        $this->footerText = $footerText;
        $this->menuBgColor = $optionsArray['menu_bg_color'];
        $this->sectionBgColor = $optionsArray['section_bg_color'];
        $this->menuBorderColor = $optionsArray['menu_border_color'];
        $this->menuFontColor = $optionsArray['menu_font_color'];
        $this->sectionFontColor = $optionsArray['section_font_color'];
        $this->menuHoverColor = $optionsArray['menu_hover_color'];
        $this->menuFontFamily = $optionsArray['menu_font_family'];
        $this->menuFontStyle = $optionsArray['menu_font_style'];
        $this->menuFontSize = $optionsArray['menu_font_size'];
        $this->menuFontWeight = $optionsArray['menu_font_weight'];
        $this->menuWidth = $optionsArray['menu_width'];
        $this->menuOrientation = $optionsArray['menu_orientation'];
    }
}
