/**
 * Clipboard Service
 */
import angular from 'angular/index'

export default class ClipboardService {
  constructor() {
    // Clipboard content
    this.clipboard = null

    this.disabled = {
      copy:  false,
      paste: true
    }
  }

  /**
   * Empty clipboard.
   */
  clear() {
    this.clipboard = null

      // Disable paste buttons
    this.disabled.paste = true
  }

  getDisabled() {
    return this.disabled
  }

  /**
   * Copy selected steps into clipboard
   *
   * @param {object}   data
   * @param {function} [callback] a callback to execute on data before add them into clipboard
   */
  copy(data, callback) {
    const tempData = angular.copy(data)

    if (typeof callback === 'function') {
      // Process data before copy them into clipboard
      callback(tempData)
    }

    // Store processed data into clipboard
    this.clipboard = tempData

    // Disabled paste buttons
    this.disabled.paste = false
  }

  /**
   * Paste data form clipboard into destination.
   *
   * @param {object}   destination
   * @param {function} [callback] a callback to execute on data to paste
   */
  paste(destination, callback) {
    // Can paste only if clipboard is not empty
    if (null !== this.clipboard) {
      const dataCopy = angular.copy(this.clipboard)

      if (typeof callback === 'function') {
        // Process data before paste them
        callback(dataCopy)
      }

      // Push processed data into
      destination.push(dataCopy)
    }
  }
}
