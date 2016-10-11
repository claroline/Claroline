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
  constructor($uibModal, ExerciseService, QuestionService, FeedbackService) {
    this.$uibModal = $uibModal
    this.ExerciseService = ExerciseService
    this.QuestionService = QuestionService
    this.FeedbackService = FeedbackService

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

    // Initialize answer data if needed
    if (null === this.questionPaper.answer) {
      this.questionPaper.answer = this.QuestionService.getTypeService(this.question.type).initAnswer()
    }

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
   * Get the user score for the Question for display.
   *
   * @return {String}
   */
  getScore() {
    let score = this.QuestionService.calculateScore(this.question, this.questionPaper)
    return score + ''
  }

  /**
   * Get the total score of the Question for display.
   *
   * @return {String}
   */
  getTotalScore() {
    return this.QuestionService.calculateTotal(this.question) + ''
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
