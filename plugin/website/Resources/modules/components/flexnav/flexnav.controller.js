/**
 * Created by panos on 3/24/16.
 */
import angular from 'angular/index'

let _$window = new WeakMap()
let _utils = new WeakMap()
let _openedDropdowns = new WeakMap()

export default class Flexnav {
  constructor (flexnavUtils, $window, $document, $scope, $element) {
    _$window.set(this, angular.element($window))
    _utils.set(this, flexnavUtils)
    _openedDropdowns.set(this, [])
    this.options = Object.assign({}, this._defaultOptions(), this.options)
    this.breakpoint = parseInt(this.options.breakpoint)
    this.mobileOpen = false
    this.collapsed = true
    this.activePageId = this.options.currentPage

    this._init($scope, $document, $element)
  }

  getWindowWidth() {
    return _$window.get(this).width()
  }

  toggleMobileMenu() {
    this.mobileOpen = !this.mobileOpen
  }

  get style() {
    return _utils.get(this).getStyle(this.styleOptions, this.menu, this.hovered)
  }

  get navStyle() {
    return _utils.get(this).getNavStyle(this.styleOptions, this.totalWidth)
  }

  get toggleStyle() {
    return _utils.get(this).getToggleStyle(this.styleOptions)
  }

  get children() {
    return this.menu.children
  }

  toggleCollapse() {
    this.collapsed = !this.collapsed
  }

  onItemClick (event, menu) {
    this.activePageId = menu.id
    this.toggleDropdown(null)
    this.options.onItemClick(event, menu)
  }


  toggleDropdown (dropdown = null) {
    let dropdowns = _openedDropdowns.get(this)
    let found = false
    if (dropdown != null) {
      dropdown.collapsed = ! dropdown.collapsed
    }
    while (dropdowns.length > 0 && !found) {
      let length = dropdowns.length
      if (dropdown == null || dropdown.parent != dropdowns[length - 1]) {
        dropdowns.pop().collapsed = true
      } else {
        found = true
      }
    }
    if (dropdown != null && !dropdown.collapsed) {
      dropdowns.push(dropdown)
    }
    _openedDropdowns.set(this, dropdowns)
  }

  _init ($scope, $document, $element) {
    $document.on('click', (event) => {
      let isDocumentClicked = $element.find('.flexnav-nav').find(event.target).length <= 0;
      if (isDocumentClicked) {
        this.toggleDropdown(null)
        $scope.$evalAsync()
      }
    })
  }

  _defaultOptions () {
    return {
      menuButtonName : 'Menu',
      buttonClass : 'menu-button',
      calcItemWidths : false,
      fullScreen : true,
      breakpoint : 800,
      onItemClick : () => {},
      buildHref: () => {},
      currentPage: null
    }
  }
}

Flexnav.$inject = [ 'flexnav.utils', '$window', '$document', '$scope', '$element' ]
