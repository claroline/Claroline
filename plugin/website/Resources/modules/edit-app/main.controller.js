export default class MainController {
  constructor($scope, websiteOptions, tinyMceConfig, websiteData) {
    this.options = websiteOptions
    this.website = websiteData
    this.tinymceConfig = tinyMceConfig
    this.tree = {}
    //Resource picker configuration
    this.resourcePickerConfig = {
      isPickerMultiSelectAllowed: false,
      callback: (nodes) => {
        Object.keys(nodes).forEach((id) => {
          let element = nodes[id]
          this.tree.updateActiveNodeFromPicker(id, element)
          $scope.$evalAsync()
        })
      }
    }

    this.pushMenuOptions = {
      containersToPush: null,
      wrapperClass: 'multilevelpushmenu_wrapper',
      menuInactiveClass: 'multilevelpushmenu_inactive',
      menuWidth: this.options.data.menuWidth,
      direction: 'rtl',
      backItemIcon: 'fa fa-angle-left',
      groupIcon: 'fa fa-angle-right',
      backText: 'Back',
      mode: 'cover',
      overlapWidth: 0,
      onItemClick: this.onItemClick.bind(this)
    }

    this.flexnavOptions = {
      onItemClick: this.onItemClick.bind(this),
      breakpoint: 800
    }
  }

  saveWebsitePage() {
    this.tree.saveActiveNode()
  }

  uploadImage($files, imageStr) {
    this.options.uploadImage($files, imageStr)
  }

  setAsHomepage() {
    this.tree.setActiveNodeAsHomepage()
  }

  createNewPage() {
    this.tree.createNewNode()
  }

  onItemClick(event, item) {
    event.preventDefault()
    this.currentClickedItem = item
  }

  updateImagePath(imageStr, isReset) {
    var newPath = null
    if (!isReset) {
      newPath = this.options[ imageStr + 'Path' ]
    }
    this.options.updateImagePath(newPath, imageStr)
  }

  menuVerticalStyle() {
    if (this.options.data.menuOrientation == 'vertical') {
      return {
        'min-height': this.contentHeight + 'px',
        'width': this.options.data.menuWidth + 'px',
        'background-color': this.options.data.menuBgColor,
        'color': this.options.data.menuFontColor,
        'font-size': this.options.data.menuFontSize + 'px',
        'font-family': this.options.data.menuFontFamily
      }
    } else {
      return {}
    }
  }

  menuButtonStyle(node) {
    var backgroundColor = this.options.data.menuBgColor
    var fontColor = this.options.data.menuFontColor
    if (node.isSection) {
      backgroundColor = this.options.data.sectionBgColor
      fontColor = this.options.data.sectionFontColor
    }
    return {'background-color': backgroundColor, 'color': fontColor, 'border-color': this.options.data.menuBorderColor}
  }

  menuResizerStyle() {
    if (this.options.data.menuOrientation == 'vertical') {
      return {height: this.contentHeight + 'px'}
    } else {
      return {}
    }
  }

  websitePreviewStyle() {
    return {
      'background-color': this.options.data.bgColor,
      'background-image': this.options.getImageStyleText('bgImage'),
      'background-position': this.options.data.bgPosition,
      'background-repeat': this.options.data.bgRepeat
    }
  }

  websitePreviewInnerStyle() {
    var style = {}
    if (!this.options.isFullScreen) {
      style[ 'width' ] = this.options.data.totalWidth + 'px'
      style[ 'margin-left' ] = 'auto'
      style[ 'margin-right' ] = 'auto'
    }
    return style
  }

  get currentPageUrl() {
    let url = ''
    if (this.currentClickedItem.resourceNode != null && this.currentClickedItem.resourceNodeType != null) {
      url = window.Routing.generate('claro_resource_open', {
        resourceType: this.currentClickedItem.resourceNodeType,
        node: this.currentClickedItem.resourceNode
      })

    }
    return url
  }

  get footerStyle() {
    return {
      height: this.options.data.footerHeight + 'px',
      'background-color': this.options.data.footerBgColor,
      'background-position': this.options.data.footerBgPosition,
      'background-repeat': this.options.data.footerBgRepeat,
      'background-image': this.options.getImageStyleText('footerBgImage')
    }
  }

  get bannerStyle() {
    return {
      height: this.options.data.bannerHeight + 'px',
      'background-color': this.options.data.bannerBgColor,
      'background-position': this.options.data.bannerBgPosition,
      'background-repeat': this.options.data.bannerBgRepeat,
      'background-image': this.options.getImageStyleText('bannerBgImage')
    }
  }
}

MainController.$inject = [ '$scope', 'websiteOptions', 'tinyMceConfig', 'website.data' ]
