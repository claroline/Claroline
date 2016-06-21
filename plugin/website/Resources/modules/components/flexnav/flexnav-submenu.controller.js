let _utils = new WeakMap()
let _$scope = new WeakMap()

export default class FlexnavSubmenu {
  constructor (utils) {
    _utils.set(this, utils)
    this.collapsed = true
    this.hovered = false
    this.correctionWidth = 0
    this.level = parseInt(this.level)
  }

  get isRootLevel () {
    return parseInt(this.level) == 0
  }

  get visible () {
    return !this.parent.collapsed
  }

  get options () {
    return this.flexnav.options
  }

  get style () {
    return _utils.get(this).getStyle(this.flexnav.styleOptions, this.menu, this.hovered || this.active)
  }

  get caretStyle () {
    return _utils.get(this).getCaretStyle(this.flexnav.styleOptions, this.menu)
  }

  get hasChildren () {
    return this.menu.children && this.menu.children.length > 0
  }

  get children () {
    return this.hasChildren ? this.menu.children : []
  }

  get active () {
    return this.flexnav.activePageId == this.menu.id
  }

  toggleCollapse () {
    this.flexnav.toggleDropdown(this)
  }

  buildHref () {
    return this.options.buildHref(this.menu)
  }

  itemClicked ($event) {
    $event.stopPropagation()
    this.flexnav.onItemClick($event, this.menu)
  }

  over () {
    this.hovered = true
  }

  leave () {
    this.hovered = false
  }
}

FlexnavSubmenu.$inject = [ 'flexnav.utils' ]
