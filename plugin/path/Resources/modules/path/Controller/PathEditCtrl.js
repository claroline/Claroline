/**
 * Path edit controller
 */

import angular from 'angular/index'

import PathBaseCtrl from './PathBaseCtrl'

export default class PathEditCtrl extends PathBaseCtrl {
  constructor($window, $route, $routeParams, url, PathService, Translator, HistoryService, ConfirmService, $scope, tinymceConfig) {
    super($window, $route, $routeParams, url, PathService)

    this.Translator = Translator
    this.historyService = HistoryService
    this.confirmService = ConfirmService

    this.tinymceOptions = tinymceConfig

    /**
     * Current state of the history stack
     * @type {object}
     */
    this.historyDisabled = HistoryService.getDisabled()

    /**
     * Do Path have pending modifications
     * @type {boolean}
     */
    this.unsaved = false

    // Initialize the step structure if needed
    if (0 === this.path.steps.length) {
      this.pathService.initialize()
    }

    // listen to path changes to update history
    const path = this.path
    $scope.$watch(() => path, (newValue) => {
      const empty   = this.historyService.isEmpty()
      const updated = this.historyService.update(newValue)

      if (!empty && updated) {
        // Initialization is already done, so mark path as unsaved for each modification
        this.unsaved = true
      }
    }, true)
  }

  /**
   * Undo last action
   */
  undo() {
    if (this.historyService.canUndo()) {
      // Inject history data
      this.historyService.undo(this.path)
    }
  }

  /**
   * Redo last action
   */
  redo() {
    if (this.historyService.canRedo()) {
      // Inject history data
      this.historyService.redo(this.path)
    }
  }

  /**
   * Save the path
   */
  save() {
    if (this.unsaved) {
      // Check for condition validity
      // Save only with there is something to change
      this.pathService
        .save()
        .then(() => {
          // Mark path as modified
          this.modified = true
          this.unsaved  = false
        })
    }
  }

  /**
   * Publish the path modifications
   */
  publish() {
    if (!this.published || this.modified) {
      // Publish if there is something to publish
      this.pathService
        .publish()
        .then(() => {
          this.modified  = false
          this.published = true
          this.unsaved   = false

          this.historyService.clear()

          if (this.path.steps[0] !== 'undefined') {
            this.pathService.goTo(this.path.steps[0])
          }
        })
    }
  }

  /**
   * Preview path into player
   */
  preview() {
    function doPreview(url) {
      if (this.modified) {
        // Path modified => modifications will not be visible before publishing so warn user
        this.confirmService.open({
          title:         this.Translator.trans('preview_with_pending_changes_title',   {}, 'path_wizards'),
          message:       this.Translator.trans('preview_with_pending_changes_message', {}, 'path_wizards'),
          confirmButton: this.Translator.trans('preview_with_pending_changes_button',  {}, 'path_wizards')
        }, () => this.window.location.href = url)
      } else {
        // Open player to preview the path
        this.window.location.href = url
      }
    }

    if (this.published) {
      // Path needs to be published at least once to be previewed
      let url = this.UrlGenerator('innova_path_player_wizard', {
        id: this.path.id
      })

      if (angular.isObject(this.currentStep) && angular.isDefined(this.currentStep.stepId)) {
        url += '#/' + this.currentStep.stepId
      }

      // Force save before exit Editor
      if (this.unsaved) {
        // Save only with there is something to change
        this.pathService
          .save()
          .then(() => {
            // Mark path as modified
            this.modified = true
            this.unsaved  = false

            doPreview.call(this, url)
          })
      } else {
        doPreview.call(this, url)
      }
    }
  }
}
