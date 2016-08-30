
export default class SummaryService {
  constructor() {
    /**
     * State of the summary
     * @type {object}
     */
    this.state = {
      opened: true,
      pinned: true
    }
  }

  /**
   * Get state
   * @returns {Object}
   */
  getState() {
    return this.state
  }

  /**
   * Toggle summary state
   */
  toggleOpened() {
    this.state.opened = !this.state.opened
  }

  /**
   * Set summary state
   * @param {Boolean} value
   */
  setOpened(value) {
    this.state.opened = value
  }

  /**
   * Toggle summary pin
   */
  togglePinned() {
    this.state.pinned = !this.state.pinned
  }

  /**
   * Set summary pin
   * @param {Boolean} value
   */
  setPinned(value) {
    this.state.pinned = value
  }
}
