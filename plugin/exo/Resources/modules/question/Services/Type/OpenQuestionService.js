import AbstractQuestionService from './AbstractQuestionService'

/**
 * Open Question Service
 * @param {FeedbackService}  FeedbackService
 * @constructor
 */
function OpenQuestionService(FeedbackService) {
  AbstractQuestionService.apply(this, arguments)

  this.FeedbackService = FeedbackService
}

// Extends AbstractQuestionCtrl
OpenQuestionService.prototype = Object.create(AbstractQuestionService.prototype)

/**
 * Initialize the answer object for the Question
 */
OpenQuestionService.prototype.initAnswer = function initAnswer() {
  return ''
}

/**
 *
 * @returns {number}
 */
OpenQuestionService.prototype.answersAllFound = function answersAllFound(question, answer) {
  var feedbackState = -1

  if ('long' !== question.typeOpen) {
    // Search used keywords in student answer
    var numAnswersFound = 0

    for (var i = 0; i < question.solutions.length; i++) {
      var solution = question.solutions[i]

      // Check in answer if the keyword as been used
      var searchFlags      = 'g' + (solution.caseSensitive ? 'i' : '')
      var searchExpression = new RegExp('\\b' + solution.word + '\\b', searchFlags)
      if (-1 !== answer.search(searchExpression)) {
        numAnswersFound++
      }
    }

    if (question.solutions.length === numAnswersFound) {
      feedbackState = this.FeedbackService.SOLUTION_FOUND
    } else if (question.solutions.length -1 === numAnswersFound) {
      feedbackState = this.FeedbackService.ONE_ANSWER_MISSING
    } else {
      feedbackState = this.FeedbackService.MULTIPLE_ANSWERS_MISSING
    }
  } else {
    feedbackState = this.FeedbackService.SOLUTION_FOUND
  }

  return feedbackState
}

/**
 * Get the correct answer from the solutions of a Question
 * For type = long we can not generate a correct answer at it requires a manual correction
 * @param   {Object} question
 * @returns {Object}
 */
OpenQuestionService.prototype.getCorrectAnswer = function getCorrectAnswer(question) {
  var answer = null

  if (question.solutions && 'long' !== question.typeOpen) {
    answer = []

    // Only get the list of required keywords
    if ('oneWord' === question.typeOpen) {
      // One word answer (get the keyword with the max score)
      var betterFound = null
      for (var i = 0; i < question.solutions.length; i++) {
        if (null === betterFound || question.solutions[i].score > betterFound.score) {
          betterFound = question.solutions[i]
        }
      }

      answer.push(betterFound)
    } else if ('short' === question.typeOpen) {
      // Short answer (display all keywords with a positive score as expected answer)
      answer = question.solutions
    }
  }

  return answer
}

export default OpenQuestionService
