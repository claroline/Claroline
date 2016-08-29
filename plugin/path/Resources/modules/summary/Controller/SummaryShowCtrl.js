/**
 * Path summary show
 */

import angular from 'angular/index'

import SummaryBaseCtrl from './SummaryBaseCtrl'

export default class SummaryShowCtrl extends SummaryBaseCtrl {
  constructor(SummaryService, PathService, UserProgressionService) {
    super(SummaryService, PathService)

    this.userProgressionService = UserProgressionService
    this.progression = this.userProgressionService.get()

    this.totalSteps = this.pathService.getTotalSteps()

    // Check if summary is displayed by default or not
    const path = this.pathService.getPath()
    if (angular.isObject(path)) {
      if (!path.summaryDisplayed) {
        this.SummaryService.setOpened(false)
        this.SummaryService.setPinned(false)
      } else {
        this.SummaryService.setOpened(true)
        this.SummaryService.setPinned(true)
      }
    }
  }

  getTotalProgression() {
    return this.userProgressionService.getTotalProgression()
  }

  getTotalProgressionPercentage() {
    return Math.round(this.getTotalProgression() * 100 / this.totalSteps) + '%'
  }
}
