/**
 * Created by panos on 4/6/16.
 */
import nodeDeleteModal from './confirm-node-delete.modal.html'

let _webtreeService = new WeakMap()
let _$uibModal = new WeakMap()
let _$scope = new WeakMap()
let _modals = new WeakMap()
export default class Webtree {
  constructor (webtreeService, $uibModal, $scope) {
    //this.menu = this
    _webtreeService.set(this, webtreeService)
    _$uibModal.set(this, $uibModal)
    _$scope.set(this, $scope)
    _modals.set(this, [])
    this.instance = this
    this.root = this.tree.pages [ 0 ]
    this.activeNode = null
    this.home = null
    this.copyNode = null
  }

  get uiOptions () {
    return {
      dropped: (event) => {
        let sourceNode = (event.source.nodesScope.$nodeScope !== null) ? event.source.nodesScope.$nodeScope.$modelValue : this.root
        let sourceIndex = event.source.index
        let destNode = (event.dest.nodesScope.$nodeScope !== null) ? event.dest.nodesScope.$nodeScope.$modelValue : this.root
        let destIndex = event.dest.index
        let currentNode = event.source.nodeScope.$modelValue
        if (sourceNode != destNode || sourceIndex != destIndex) {
          this.moveNode(currentNode, sourceNode, destNode, sourceIndex, destIndex)
        }
      }
    }
  }

  get isEmpty () {
    return this.root.children.length == 0
  }

  get hasActiveNode () {
    return this.activeNode && !this.activeNode.new && this.activeNode != this.root
  }

  createNewNode (parent) {
    parent = parent || this.root
    this.setActiveNode(_webtreeService.get(this).appendNewNode(parent))
  }

  createSubNode (parent) {
    if (parent) {
      this.createNewNode(parent)
    }
  }

  setActiveNode (node) {
    if (this.activeNode && this.activeNode.new) {
      _webtreeService.get(this).deleteNode(this.activeNode)
    } else if (this.activeNode) {
      if (!this.activeNode.saving && this.copyNode != null) {
        Object.assign(this.activeNode, this.copyNode)
      }
    }
    this.copyNode = null
    if (node && !node.new) this.copyNode = _webtreeService.get(this).copyNodeInfo(node)
    this.activeNode = node
  }

  deleteActiveNode (confirmed = false) {
    if (this.hasActiveNode) {
      if (!confirmed) {
        _modals.get(this).push(_$uibModal.get(this).open(
          {
            scope: _$scope.get(this),
            template: nodeDeleteModal
          }
        ))
      } else {
        this.dismissModal()
        _webtreeService.get(this).deleteNode(this.activeNode).then(
          (node) => {
            if (this.home == node) {
              this.home = null
            }
            if (this.activeNode == node) {
              if (node.parent == this.root && this.root.children.length > 0) {
                this.setActiveNode(this.root.children[0])
              } else if (node.parent != this.root) {
                this.setActiveNode(node.parent)
              } else {
                this.setActiveNode(null)
              }
            }
          }
        )
      }
    }
  }

  dismissModal () {
    let modal = _modals.get(this).pop()
    if (modal) {
      modal.dismiss()
    }
  }

  moveNode (node, oldParent, newParent, oldIndex, newIndex) {
    if (oldParent == null) oldParent = this.root
    if (newParent == null) newParent = this.root

    _webtreeService.get(this).moveNode(node, oldParent, newParent, oldIndex, newIndex)
  }

  saveActiveNode () {
    if (this.activeNode) {
      let websiteService = _webtreeService.get(this)
      let copy = null
      let isEmpty = this.activeNode.new && this.root.children.length == 1
      if (!this.activeNode.new) {
        copy = websiteService.copyNodeInfo(this.activeNode)
      }
      websiteService.saveNode(this.activeNode).then(
        node => {
          if (isEmpty && copy == null) {
            node.isHomepage = true
            this.home = node
          }
          this.copyNode = null
        },
        node => {
          if (node.id == 0 && this.activeNode != node) {
            websiteService.deleteNode(node)
          }
          if (copy != null && this.activeNode != node) {
            Object.assign(node, copy)
            this.copyNode = null
          }
        }
      )
    }
  }

  setActiveNodeAsHomepage () {
    if (this.activeNode) {
      _webtreeService.get(this).setHomepage(this.activeNode, this.home).then(
        newHomepage => {
          this.home = newHomepage
        }
      )
    }
  }

  updateActiveNodeFromPicker (id, data) {
    this.activeNode.resourceNode = id
    this.activeNode.title = data[ 0 ]
    this.activeNode.resourceNodeType = data[ 1 ]
    this.activeNode.resourceNodeName = data[ 0 ]
    this.activeNode.resourceNodeWorkspace = null
  }
}

Webtree.$inject = [ 'webtreeService', '$uibModal', '$scope' ]