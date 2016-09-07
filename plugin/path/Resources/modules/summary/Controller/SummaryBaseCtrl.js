/**
 * Base controller for Summary
 */

import angular from 'angular/index'

export default class SummaryBaseCtrl {
  constructor(SummaryService, PathService) {
    this.SummaryService = SummaryService
    this.pathService = PathService

    /**
     * Sate of the summary
     * @type {boolean}
     */
    this.state = this.SummaryService.getState()

    /**
     * Structure of the current path
     * @type {array}
     */
    this.structure = []

    // Get the structure of the current path
    const path = this.pathService.getPath()
    if (angular.isObject(path)) {
      // Set the structure of the path
      this.structure = path.steps
    }
  }

  /**
   * Close Summary
   */
  close() {
    this.SummaryService.setOpened(false)
  }

  toggleOpened() {
    this.SummaryService.toggleOpened()
  }

  togglePinned() {
    this.SummaryService.togglePinned()
  }

  getPsPushClass() {
    return this.state.pinned ? 'path-summary-opened' : ''
  }
}
