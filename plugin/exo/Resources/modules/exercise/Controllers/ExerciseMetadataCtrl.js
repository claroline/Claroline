
import angular from 'angular/index'

/**
 * Exercise Metadata Controller
 * Manages edition of the parameters of the Exercise
 */
export default class ExerciseMetadataCtrl {
  /**
   * Constructor.
   *
   * @param {Object}          $location
   * @param {ExerciseService} ExerciseService
   * @param {TinyMceService}  TinyMceService
   * @param {CorrectionMode}  CorrectionMode
   * @param {MarkMode}        MarkMode
   */
  constructor($location, ExerciseService, TinyMceService, CorrectionMode, MarkMode) {
    this.$location       = $location
    this.ExerciseService = ExerciseService
    this.TinyMceService  = TinyMceService
    this.CorrectionMode  = CorrectionMode
    this.MarkMode        = MarkMode

    /**
     * A copy of the Exercise to edit (to not override Exercise data if User cancel the edition)
     * @type {Object}
     */
    this.meta = {}
    angular.copy(this.ExerciseService.getMetadata(), this.meta)

    /**
     * List of available correction modes for Exercise
     * @type {Object}
     */
    this.correctionModes = this.CorrectionMode.getList()

    /**
     * List of available mark modes for Exercise
     * @type {Object}
     */
    this.markModes       = this.MarkMode.getList()

    /**
     * Tiny MCE options
     * @type {object}
     */
    this.tinymceOptions = TinyMceService.getConfig()
  }

  /**
   * Save modifications of the Exercise
   */
  save() {
    this.ExerciseService
      .save(this.meta)
      .then(() => {
        // Go back on the overview
        this.$location.path('/')
      })
  }
}
