export default class FlexnavOptions {
  construct() {
    this.menuButtonName = 'Menu'
    this.buttonClass = 'menu-button'
    this.calcItemWidths = false
    this.fullScreen = true
    this.breakpoint = 800
    this.onItemClick = () => {}
  }
}