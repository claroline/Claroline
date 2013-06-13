<?php

namespace Claroline\CoreBundle\Library\Themes;

class ThemeParameters
{
    private $colors;
    private $parameters;

    public function __construct()
    {
        $this->colors = $this->colors();

        $this->parameters = array(
            "Scaffolding" => $this->scaffolding(),
            "Links" => $this->links(),
            "Typography" => $this->typography(),
            "Sizing" => $this->sizing(),
            "Tables" => $this->tables(),
            "Buttons" => $this->buttons(),
            "Forms" => $this->forms(),
            "Dropdowns" => $this->dropdowns(),
            "Components" => $this->components(),
            "Navbar" => $this->navbar(),
            "Inverted Navbar" => $this->invertednavbar(),
            "Pagination" => $this->pagination(),
            "Hero Unit" => $this->herounit(),
            "Alerts" => $this->alerts(),
            "Tooltip & Popovers" => $this->popovers()
        );
    }

    public function getColors()
    {
        return $this->colors;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function colors()
    {
        return array(
            "@black" => "#000",
            "@grayDarker" => "#222",
            "@grayDark" => "#333",
            "@gray" => "#555",
            "@grayLight" => "#999",
            "@grayLighter" => "#eee",
            "@white" => "#fff",
            "@blue" => "#049cdb",
            "@blueDark" => "#0064cd",
            "@green" => "#46a546",
            "@red" => "#9d261d",
            "@yellow" => "#ffc40d",
            "@orange" => "#f89406",
            "@pink" => "#c3325f",
            "@purple" => "#7a43b6"
        );
    }

    public function scaffolding()
    {
        return array(
            "@bodyBackground" => "@white",
            "@textColor" => "@grayDark"
        );
    }

    public function links()
    {
        return array(
            "@linkColor" => "#08c",
            "@linkColorHover" => "darken(@linkColor, 15%)"
        );
    }

    public function typography()
    {
        return array(
            "@sansFontFamily" => "'Helvetica Neue', Helvetica, Arial, sans-serif",
            "@serifFontFamily" => "Georgia, 'Times New Roman', Times, serif",
            "@monoFontFamily" => "Monaco, Menlo, Consolas, 'Courier New', monospace",
            "@baseFontSize" => "14px",
            "@baseFontFamily" => "@sansFontFamily",
            "@baseLineHeight" => "20px",
            "@altFontFamily" => "@serifFontFamily",
            "@headingsFontFamily" => "inherit",
            "@headingsFontWeight" => "bold",
            "@headingsColor" => "inherit"
        );
    }

    public function sizing()
    {
        return array(
            "@fontSizeLarge" => "@baseFontSize * 1.25",
            "@fontSizeSmall" => "@baseFontSize * 0.85",
            "@fontSizeMini" => "@baseFontSize * 0.75",
            "@paddingLarge" => "11px 19px",
            "@paddingSmall" => "2px 10px",
            "@paddingMini" => "0 6px",
            "@baseBorderRadius" => "4px",
            "@borderRadiusLarge" => "6px",
            "@borderRadiusSmall" => "3px"
        );
    }

    public function tables()
    {
        return array(
            "@tableBackground" => "transparent",
            "@tableBackgroundAccent" => "#f9f9f9",
            "@tableBackgroundHover" => "#f5f5f5",
            "@tableBorder" => "#ddd"
        );
    }

    public function buttons()
    {
        return array(
            "@btnBackground" => "@white",
            "@btnBackgroundHighlight" => "darken(@white, 10%)",
            "@btnBorder" => "#ccc",
            "@btnPrimaryBackground" => "@linkColor",
            "@btnPrimaryBackgroundHighlight" => "spin(@btnPrimaryBackground, 20%)",
            "@btnInfoBackground" => "#5bc0de",
            "@btnInfoBackgroundHighlight" => "#2f96b4",
            "@btnSuccessBackground" => "#62c462",
            "@btnSuccessBackgroundHighlight" => "#51a351",
            "@btnWarningBackground" => "lighten(@orange, 15%)",
            "@btnWarningBackgroundHighlight" => "@orange",
            "@btnDangerBackground" => "#ee5f5b",
            "@btnDangerBackgroundHighlight" => "#bd362f",
            "@btnInverseBackground" => "#444",
            "@btnInverseBackgroundHighlight" => "@grayDarker"
        );
    }

    public function forms()
    {
        return array(
            "@inputBackground" => "@white",
            "@inputBorder" => "#ccc",
            "@inputBorderRadius" => "@baseBorderRadius",
            "@inputDisabledBackground" => "@grayLighter",
            "@formActionsBackground" => "#f5f5f5",
            "@inputHeight" => "@baseLineHeight + 10px"
        );
    }

    public function dropdowns()
    {
        return array(
            "@dropdownBackground" => "@white",
            "@dropdownBorder" => "rgba(0,0,0,.2)",
            "@dropdownDividerTop" => "#e5e5e5",
            "@dropdownDividerBottom" => "@white",
            "@dropdownLinkColor" => "@grayDark",
            "@dropdownLinkColorHover" => "@white",
            "@dropdownLinkColorActive" => "@white",
            "@dropdownLinkBackgroundActive" => "@linkColor",
            "@dropdownLinkBackgroundHover" => "@dropdownLinkBackgroundActive"
        );
    }

    public function components()
    {
        return array(
            "@zindexDropdown" => "1000",
            "@zindexPopover" => "1010",
            "@zindexTooltip" => "1030",
            "@zindexFixedNavbar" => "1030",
            "@zindexModalBackdrop" => "1040",
            "@zindexModal" => "1050",
            "@placeholderText" => "@grayLight",
            "@hrBorder" => "@grayLighter",
            "@horizontalComponentOffset" => "180px",
            "@wellBackground" => "#f5f5f5"
        );
    }

    public function navbar()
    {
        return array(
            "@navbarCollapseWidth" => "979px",
            "@navbarCollapseDesktopWidth" => "@navbarCollapseWidth + 1",
            "@navbarHeight" => "40px",
            "@navbarBackgroundHighlight" => "#ffffff",
            "@navbarBackground" => "darken(@navbarBackgroundHighlight, 5%)",
            "@navbarBorder" => "darken(@navbarBackground, 12%)",
            "@navbarText" => "#777",
            "@navbarLinkColor" => "#777",
            "@navbarLinkColorHover" => "@grayDark",
            "@navbarLinkColorActive" => "@gray",
            "@navbarLinkBackgroundHover" => "transparent",
            "@navbarLinkBackgroundActive" => "darken(@navbarBackground, 5%)"
        );
    }

    public function invertednavbar()
    {
        return array(
            "@navbarInverseBackground" => "#111111",
            "@navbarInverseBackgroundHighlight" => " #222222",
            "@navbarInverseBorder" => "#252525",
            "@navbarInverseText" => "@grayLight",
            "@navbarInverseLinkColor" => " @grayLight",
            "@navbarInverseLinkColorHover" => "@white",
            "@navbarInverseLinkColorActive" => " @navbarInverseLinkColorHover",
            "@navbarInverseLinkBackgroundHover" => " transparent",
            "@navbarInverseLinkBackgroundActive" => "@navbarInverseBackground",
            "@navbarInverseSearchBackground" => "lighten(@navbarInverseBackground, 25%)",
            "@navbarInverseSearchBackgroundFocus" => " @white",
            "@navbarInverseSearchBorder" => "@navbarInverseBackground",
            "@navbarInverseSearchPlaceholderColor" => "#ccc",
            "@navbarInverseBrandColor" => "@navbarInverseLinkColor"
        );
    }

    public function pagination()
    {
        return array(
            "@paginationBackground" => "#fff",
            "@paginationBorder" => "#ddd",
            "@paginationActiveBackground" => "#f5f5f5"
        );
    }

    public function herounit()
    {
        return array(
            "@heroUnitBackground" => "@grayLighter",
            "@heroUnitHeadingColor" => "inherit",
            "@heroUnitLeadColor" => "inherit"
        );
    }

    public function alerts()
    {
        return array(
            "@warningText" => "#c09853",
            "@warningBackground" => "#fcf8e3",
            "@warningBorder" => "darken(spin(@warningBackground, -10), 3%)",
            "@errorText" => "#b94a48",
            "@errorBackground" => "#f2dede",
            "@errorBorder" => "darken(spin(@errorBackground, -10), 3%)",
            "@successText" => "#468847",
            "@successBackground" => "#dff0d8",
            "@successBorder" => "darken(spin(@successBackground, -10), 5%)",
            "@infoText" => "#3a87ad",
            "@infoBackground" => "#d9edf7",
            "@infoBorder" => "darken(spin(@infoBackground, -10), 7%)"
        );
    }

    public function popovers()
    {
        return array(
            "@tooltipColor" => "#fff",
            "@tooltipBackground" => "#000",
            "@tooltipArrowWidth" => "5px",
            "@tooltipArrowColor" => "@tooltipBackground",
            "@popoverBackground" => "#fff",
            "@popoverArrowWidth" => "10px",
            "@popoverArrowColor" => "#fff",
            "@popoverTitleBackground" => "darken(@popoverBackground, 3%)",
            "@popoverArrowOuterWidth" => "@popoverArrowWidth + 1",
            "@popoverArrowOuterColor" => "rgba(0,0,0,.25)"
        );
    }
}
