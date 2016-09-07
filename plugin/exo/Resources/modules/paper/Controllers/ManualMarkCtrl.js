/**
 * Controller used to let the admins mark questions
 */
export default class ManualMarkCtrl {
  /**
   * Constructor.
   *
   * @param {object} $uibModalInstance
   * @param {PaperService} PaperService
   * @param {QuestionService} QuestionService
   * @param {Object} question
   */
  constructor($uibModalInstance, PaperService, QuestionService, question) {
    this.$uibModalInstance = $uibModalInstance
    this.PaperService = PaperService
    this.QuestionService = QuestionService

    this.question = question

    this.score = null
    this.scoreTotal = this.QuestionService.getTypeService(this.question.type).getTotalScore(this.question)
    
    /**
     * An error message if the given score is incorrect (eg. greater than the question total score).
     *
     * @type {null}
     */
    this.errors = []
  }

  /**
   * Save mark
   */
  save() {
    this.errors.splice(0, this.errors.length)
    if (this.score > this.scoreTotal) {
      this.errors.push('mark_bigest')
    } else {
      this.PaperService
        .saveScore(this.question, this.score)
        .then(() => {
          this.score = null

          // Go back on the paper
          this.$uibModalInstance.close()
        })
    }
  }

  cancel() {
    this.score = null
    this.$uibModalInstance.dismiss('cancel')
  }
}
