
const elementSelector = '.please-wait'

export class LoadingIndicator {
  static show() {
    document.querySelector(elementSelector).style.display = 'block'
  }

  static hide() {
    document.querySelector(elementSelector).style.display = 'none'
  }
}
