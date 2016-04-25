/**
 * Created by panos on 4/6/16.
 */
export default class WebtreeNode {
  constructor ($timeout) {
    this.collapsed = false
    this.saving = false
    //Initialize after rendering
    $timeout(this.init.bind(this), 0)
  }
  init () {
    if (this.node.isHomepage) {
      this.setAsActive()
      this.webtree.home = this.node
    } else if (this.webtree.activeNode == null) {
      this.setAsActive()
    }
  }
  toggleCollapse () {
    this.collapsed = !this.collapsed
  }

  get marginLeft () {
    return ((parseInt(this.depth) - 1) * 15) + 'px'
  }

  setAsActive () {
    this.webtree.setActiveNode(this.node)
  }
}

WebtreeNode.$inject = [ '$timeout' ]