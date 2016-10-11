/**
 * Question Service
 */
export default class QuestionService {
  /**
   * Constructor.
   *
   * @param {object} $log
   * @param {ChoiceQuestionService}  ChoiceQuestionService
   * @param {ClozeQuestionService}   ClozeQuestionService
   * @param {GraphicQuestionService} GraphicQuestionService
   * @param {MatchQuestionService}   MatchQuestionService
   * @param {OpenQuestionService}    OpenQuestionService
   */
  constructor(
    $log,
    ChoiceQuestionService,
    ClozeQuestionService,
    GraphicQuestionService,
    MatchQuestionService,
    OpenQuestionService
  ) {
    this.$log = $log
    this.services = {}

    // Inject custom services
    this.services['application/x.choice+json']  = ChoiceQuestionService
    this.services['application/x.match+json']   = MatchQuestionService
    this.services['application/x.cloze+json']   = ClozeQuestionService
    this.services['application/x.short+json']   = OpenQuestionService
    this.services['application/x.graphic+json'] = GraphicQuestionService
  }

  /**
   * Get the Service that manage the QuestionType.
   *
   * @return {AbstractQuestionService}
   */
  getTypeService(questionType) {
    let service = null
    if (!this.services[questionType]) {
      this.$log.error('Question Type : try to get a Service for an undefined type `' + questionType + '`.')
    } else {
      service = this.services[questionType]
    }

    return service
  }

  /**
   * Get a Question from its ID
   * @param {Array} questions
   * @param {String} id
   * @returns {Object}
   */
  getQuestion(questions, id) {
    let question = null
    for (let i = 0; i < questions.length; i++) {
      if (id === questions[i].id) {
        question = questions[i]
        break // Stop searching
      }
    }

    return question
  }

  calculateScore(question, questionPaper) {
    let score = 0
    if (questionPaper.score || 0 === questionPaper.score) {
      score = questionPaper.score
    } else {
      score = this.getTypeService(question.type).getAnswerScore(question, questionPaper.answer)
      // Apply hints penalties
      if (questionPaper.hints) {
        for (let i = 0; i < questionPaper.hints.length; i++) {
          score -= questionPaper.hints[i].penalty
        }
      }
    }

    return score
  }

  calculateTotal(question) {
    return this.getTypeService(question.type).getTotalScore(question)
  }
}
