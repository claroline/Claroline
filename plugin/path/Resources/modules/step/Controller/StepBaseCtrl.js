/**
 * Step base controller
 */
export default class StepBaseCtrl {
  constructor(step, inheritedResources, PathService, SummaryService) {
    this.pathService = PathService

    /**
     * Sate of the summary
     * @type {boolean}
     */
    this.summaryState = SummaryService.getState()

    /**
     * Current step
     * @type {object}
     */
    this.step = step

    /**
     * Inherited resources from parents of the Step
     * @type {array}
     */
    this.inheritedResources = inheritedResources

    /**
     * Previous step
     * @type {object}
     */
    this.previous = this.pathService.getPrevious(step)

    /**
     * Next step
     * @type {object}
     */
    this.next = this.pathService.getNext(step)

    /**
     * Path
     * @type {object}
     */
    this.path = this.pathService.getPath()
  }

  /**
   * Wrapper for the goTo method (used to jump to next or previous step)
   * @param step
   */
  goTo(step) {
    this.pathService.goTo(step)
  }
}
