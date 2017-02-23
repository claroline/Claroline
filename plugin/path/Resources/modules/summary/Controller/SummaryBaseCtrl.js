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

      // Check if summary is displayed by default or not
      if (!path.summaryDisplayed) {
        this.SummaryService.setOpened(false)
        this.SummaryService.setPinned(false)
      } else {
        this.SummaryService.setOpened(true)
        this.SummaryService.setPinned(true)
      }
    }
  }

  toggleOpened() {
    this.SummaryService.toggleOpened()
  }

  togglePinned() {
    this.SummaryService.togglePinned()
  }
}
