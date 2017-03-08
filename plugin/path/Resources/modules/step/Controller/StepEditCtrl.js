/**
 * Step edit controller
 */

import StepBaseCtrl from './StepBaseCtrl'

export default class StepEditCtrl extends StepBaseCtrl {
  constructor(step, inheritedResources, PathService, SummaryService, url, $scope, StepService, tinymceConfig) {
    super(step, inheritedResources, PathService, SummaryService)

    this.scope       = $scope
    this.stepService = StepService
    this.UrlGenerator = url

    /**
     * Defines which panels of the form are collapsed or not
     * @type {object}
     */
    this.collapsedPanels = {
      description       : false,
      properties        : true,
      conditions        : true
    }

    /**
     * Tiny MCE options
     * @type {object}
     */
    this.tinymceOptions = tinymceConfig

    /**
     * Activity resource picker config
     * @type {object}
     */
    this.resourcePicker = {
      // A step can allow be linked to one Activity, so disable multi-select
      isPickerMultiSelectAllowed: false,

      // Only allow Activity selection
      typeWhiteList: ['activity'],
      callback: (nodes) => {
        if (typeof nodes === 'object' && nodes.length !== 0) {
          for (var nodeId in nodes) {
            if (nodes.hasOwnProperty(nodeId)) {
              // Load activity properties to populate step
              this.stepService.loadActivity(this.step, nodeId)

              break // We need only one node, so only the last one will be kept
            }
          }

          this.scope.$apply()

          // Remove checked nodes for next time
          nodes = {}
        }
      }
    }
  }

  /**
   * Display activity linked to the Step
   */
  showActivity() {
    const activityRoute = this.UrlGenerator('innova_path_show_activity', {
      activityId: this.step.activityId
    })

    window.open(activityRoute, '_blank')
  }

  /**
   * Delete the link between the Activity and the Step (Step's data are kept)
   */
  deleteActivity() {
    this.step.activityId = null
  }
}
