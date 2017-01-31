let _$location = new WeakMap()
let _Messages = new WeakMap()
let _transFilter = new WeakMap()

export default class TestController {

  constructor(wikiService, treeService, $location, Messages, transFilter) {
    this.wiki = wikiService
    this.treeService = treeService
    _$location.set(this, $location)
    _Messages.set(this, Messages)
    _transFilter.set(this, transFilter)

    this.dragEnabled = false
  }

  get treeOptions() {
    return {
      beforeDrop: this._dragSection.bind(this)
    }
  }

  goToSection(id) {
    // Links are only clickable in a not draggable state
    if (!this.dragEnabled) {
      _$location.get(this).hash(`sect-${id}`)
    }
  }

  _dragSection(event) {
    this.dragEnabled = false

    // Which section id dragged?
    let section = event.source.nodeScope.$modelValue

    // Find its new parent and new previous sibling
    let newParent = null
    let newPreviousSibling = null

    if (event.dest.nodesScope.$parent.$type === 'uiTreeNode') { // The new parent is not the root section
      // Find the new parent
      newParent = event.dest.nodesScope.$parent.$modelValue

      if (event.dest.index > 0) { // If the section is not the first child
        // Find the new previous sibling
        newPreviousSibling = event.dest.nodesScope.$parent.$modelValue.__children[event.dest.index - 1]
      }
    } else { // The parent is the root section

      // Find the new previous sibling
      if (event.dest.index > 0) {
        newPreviousSibling = this.wiki.sections[0].__children[event.dest.index - 1]
      }
    }

    // Save on server
    this.treeService.moveSection(this.wiki, section, newParent, newPreviousSibling).then(
      () => {},
      () => {
        this.dragEnabled = false
        this._setMessage('danger', 'icap_wiki_section_move_error')
      }
    ).finally(() => {
      this.dragEnabled = true
    })
  }

  _setMessage(type, msg) {
    _Messages.get(this).push({
      type: type,
      msg: _transFilter.get(this)(msg, {}, 'icap_wiki')
    })
  }

}

TestController.$inject = [
  'WikiService',
  'TreeService',
  '$location',
  'Messages',
  'transFilter'
]
