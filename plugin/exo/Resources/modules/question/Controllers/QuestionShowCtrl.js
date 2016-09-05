import markTpl from './../../paper/Partials/manual-mark.html'

export default class QuestionShowCtrl {
  /**
   * Constructor.
   *
   * @param {object} $uibModal
   * @param {ExerciseService} ExerciseService
   * @param {QuestionService} QuestionService
   * @param {FeedbackService} FeedbackService
   */
  constructor($uibModal, ExerciseService, FeedbackService) {
    this.$uibModal = $uibModal
    this.ExerciseService  = ExerciseService
    this.FeedbackService  = FeedbackService

    /**
     * Is the Question panel collapsed ?
     * @type {boolean}
     */
    this.collapsed = false

    /**
     * Is edit enabled ?
     * @type {boolean}
     */
    this.editEnabled = this.ExerciseService.isEditEnabled()

    /**
     * Feedback information
     * @type {Object}
     */
    this.feedback = this.FeedbackService.get()

    // Force the feedback when correction is shown
    if (this.includeCorrection && !this.FeedbackService.isEnabled()) {
      this.FeedbackService.enable()
      this.FeedbackService.show()
    }
  }

  /**
   * Mark the question
   */
  mark() {
    this.$uibModal.open({
      template: markTpl,
      controller: 'ManualMarkCtrl as manualMarkCtrl',
      resolve: {
        question: () => {
          return this.question
        }
      }
    })
  }

  /**
   * Get the generic feedback
   * @returns {string}
   */
  getGenericFeedback() {
    if (this.feedback.state[this.question.id] === 1) {
      return 'one_answer_to_find'
    } else if (this.feedback.state[this.question.id] === 2) {
      return 'answers_not_found'
    }
  }
}
