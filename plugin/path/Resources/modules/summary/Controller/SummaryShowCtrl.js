/**
 * Path summary show
 */

import SummaryBaseCtrl from './SummaryBaseCtrl'

export default class SummaryShowCtrl extends SummaryBaseCtrl {
  constructor(SummaryService, PathService, UserProgressionService) {
    super(SummaryService, PathService)

    this.userProgressionService = UserProgressionService
    this.progression = this.userProgressionService.get()
  }
}
