/**
 * History Factory
 */

import angular from 'angular/index'

export default class HistoryService {
  constructor() {
    this.disabled = {
      redo: true,
      undo: true
    }

    /**
     * History stack
     *
     * @type {Array}
     */
    this.history = []

    /**
     * Index of current data into the history
     *
     * @type {number}
     */
    this.historyIndex = -1
  }

  getDisabled() {
    return this.disabled
  }

  /**
   * Can the last action be undo ?
   *
   * @returns {boolean}
   */
  canUndo() {
    return !this.disabled.undo
  }

  /**
   * Can next action be redo ?
   *
   * @returns {boolean}
   */
  canRedo() {
    return !this.disabled.redo
  }

  /**
   * Is history empty ?
   *
   * @returns {boolean}
   */
  isEmpty() {
    return -1 == this.historyIndex
  }

  /**
   * Restore default history state (= empty history)
   */
  clear() {
    this.disabled.redo = true
    this.disabled.undo = true

    this.history = []
    this.historyIndex = -1
  }

  /**
   * Store current data in history
   *
   * @param   {object} data
   *
   * @returns {boolean}
   */
  update(data) {
    let updated = false

    // Get the last data stored in history to show if something has changed
    const lastHistoryData     = this.getFromHistory(this.historyIndex)
    const lastHistoryDataJson = angular.toJson(lastHistoryData) // Convert to JSON to compare to current data

    // Create a clean copy of current data (e.g. without Angular JS custom properties)
    const dataCopy     = angular.copy(data)
    const dataCopyJson = angular.toJson(dataCopy) // Convert to JSON to compare to last history

    if (this.isEmpty() || lastHistoryDataJson != dataCopyJson) {
      // There are changes into data => add them to history
      // Increment history state
      this.incrementIndex()

      // Store a copy of data in history stack
      this.history.push(dataCopy)

      if (this.getIndex() !== 0) {
        // History is not empty => enable the undo function
        this.disabled.undo = false
      }
      this.disabled.redo = true

      updated = true
    }

    return updated
  }

  /**
   * Get the last path state from history stack and set it as current path.
   *
   * @param {Object} currentData
   *
   * @returns {Object}
   */
  undo(currentData) {
    // Decrement history state
    this.decrementIndex()

    var data = this.getFromHistory(this.historyIndex)

    this.disabled.redo = false
    if (0 <= this.historyIndex) {
      // History stack is empty => disable the undo function
      this.disabled.undo = true
    }

    return this.restoreData(currentData, data)
  }

  /**
   * Get the next history state from history stack and set it as current path.
   *
   * @param {Object} currentData
   *
   * @returns {Object}
   */
  redo(currentData) {
    this.incrementIndex()

    var data = this.getFromHistory(this.historyIndex)

    this.disabled.undo = false
    if (this.historyIndex == this.history.length - 1) {
      this.disabled.redo = true
    }

    return this.restoreData(currentData, data)
  }

  /**
   * Increment history index.
   */
  incrementIndex() {
    // Increment history state
    this.setIndex(this.getIndex() + 1)
  }

  /**
   * Decrement history index
   */
  decrementIndex() {
    // Decrement history state
    this.setIndex(this.getIndex() - 1)
  }

  /**
   * Get history index.
   *
   * @returns {number}
   */
  getIndex() {
    return this.historyIndex
  }

  /**
   * Set history index.
   *
   * @param   {number} newIndex
   *
   * @returns {*}
   */
  setIndex(newIndex) {
    this.historyIndex = newIndex
  }

  /**
   * Get state stored at position index in history stack.
   *
   * @param   {number} index
   *
   * @returns {object}
   */
  getFromHistory(index) {
    let data = null

    if (typeof this.history[index] !== 'undefined') {
      data = this.history[index]
    }

    return data
  }

  restoreData(destination, source) {
    // Empty the destination object (we need to keep the reference to original object)
    for (let prop in destination) {
      if (destination.hasOwnProperty(prop)) {
        delete destination[prop]
      }
    }

    // Copy data into destination object
    angular.extend(destination, source)

    return destination
  }
}
