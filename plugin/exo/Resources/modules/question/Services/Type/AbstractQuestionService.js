/**
 * Base question service
 * @constructor
 */
export default class AbstractQuestionService {
  /**
   * Constructor.
   * 
   * @param {Object} $log
   */
  constructor($log, FeedbackService) {
    this.$log = $log
    this.FeedbackService = FeedbackService
  }
  
  /**
   * Initialize the answer object for the Question
   */
  initAnswer() {
    this.$log.error('Each instance of AbstractQuestionType must implement the `initAnswer`.')
  }

  /**
   * Get the correct answer from the solutions of a Question
   * @returns {Object|Array}
   */
  getCorrectAnswer() {
    this.$log.error('Each instance of AbstractQuestionType must implement the `getCorrectAnswer(question)`.')
  }

  getTotalScore() {
    this.$log.error('Each instance of AbstractQuestionType must implement the `getTotalScore(question)`.')
  }

  getAnswerScore() {
    this.$log.error('Each instance of AbstractQuestionType must implement the `getAnswerScore(question, answer)`.')
  }
}
