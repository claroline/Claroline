import template from './../Partial/edit-item.html'

export default class SummaryItemEditDirective {
  constructor() {
    this.restrict = 'E'
    this.replace = true
    this.template = template
    this.controller = [
      '$routeParams',
      'Translator',
      'ClipboardService',
      'ConfirmService',
      'IdentifierService',
      'PathService',
      function ($routeParams, Translator, ClipboardService, ConfirmService, IdentifierService, PathService) {
        /**
         * Current displayed Step
         * @type {object}
         */
        this.current = $routeParams

        this.collapsed = false

        /**
         * Maximum depth of the Path
         * @type {number}
         */
        this.maxDepth = PathService.getMaxDepth()

        /**
         * Current state of the clipboard
         * @type {object}
         */
        this.clipboardDisabled = ClipboardService.getDisabled()

        this.areActionsShown = function (show) {
          return show || this.current.stepId == this.step.id || (!this.current.stepId && 0 == this.step.lvl)
        }

        /**
         * Go to a specific Step
         * @param step
         */
        this.goTo = function () {
          PathService.goTo(this.step)
        }

        /**
         * Add a new step child to specified step
         */
        this.addStep = function () {
          PathService.addStep(this.step, true)
        }

        /**
         * Copy step into clipboard
         */
        this.copy = function () {
          ClipboardService.copy(this.step)
        }

        /**
         * Paste clipboard content
         */
        this.paste = function () {
          // Paste clipboard content into children of the step
          ClipboardService.paste(this.step.children, (clipboardData) => {
            // Change step IDs before paste them
            PathService.browseSteps([clipboardData], (parentStep, step) => {
              step.id = IdentifierService.generateUUID()

              // Reset server step ID
              step.resourceId = null
              // Reset Activity ID to generate a new one when publishing path
              step.activityId = null

              step.lvl = parentStep ? parentStep.lvl + 1 : this.step.lvl + 1

              // Override name
              step.name  = step.name ? step.name + ' ' : ''
              step.name += '(' + Translator.trans('copy', {}, 'path_wizards') + ')'
            })
          })
        }

        /**
         * Remove a step from Tree
         */
        this.removeStep = function () {
          if (0 !== this.step.lvl) {
            ConfirmService.open({
              title:         Translator.trans('step_delete_title',   { stepName: this.step.name }, 'path_wizards'),
              message:       Translator.trans('step_delete_confirm', {}                     , 'path_wizards'),
              confirmButton: Translator.trans('step_delete',         {}                     , 'path_wizards')
            },
              // Confirm success callback
              () => {
                // Check if we are deleting the current editing step
                let updatePreview = false
                if (this.step.id == this.current.stepId) {
                  // Need to update preview
                  updatePreview = true
                }

                // Effective remove
                PathService.removeStep(this.step, updatePreview)
              }
            )
          }
        }
      }
    ]
    this.controllerAs = 'summaryItemEditCtrl'
    this.bindToController = true
    this.scope = {
      step: '='
    }
  }
}
