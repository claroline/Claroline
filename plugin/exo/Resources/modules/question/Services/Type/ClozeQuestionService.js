import AbstractQuestionService from './AbstractQuestionService'

/**
 * Cloze Question Service
 * @param {FeedbackService} FeedbackService
 * @constructor
 */
function ClozeQuestionService(FeedbackService) {
  AbstractQuestionService.apply(this, arguments)

  this.FeedbackService = FeedbackService
}

// Extends AbstractQuestionCtrl
ClozeQuestionService.prototype = Object.create(AbstractQuestionService.prototype)

/**
 * Initialize the answer object for the Question
 */
ClozeQuestionService.prototype.initAnswer = function initAnswer() {
  return []
}

ClozeQuestionService.prototype.answersAllFound = function answersAllFound(question, answers) {
  var feedbackState = -1

  if (question.solutions) {
    var numAnswersFound = 0

    for (var i=0; i<question.solutions.length; i++) {
      for (var j=0; j<question.solutions[i].answers.length; j++) {
        for (var k=0; k<question.holes.length; k++) {
          for (var l=0; l<answers.length; l++) {
            if (answers[l].holeId === question.solutions[i].holeId) {
              var answer = answers[l]
            }
          }
          if (question.holes[k].id === question.solutions[i].holeId && question.solutions[i].answers[j].text === answer.answerText && question.solutions[i].answers[j].score > 0 && !question.holes[k].selector) {
            numAnswersFound++
          } else if (question.holes[k].id === question.solutions[i].holeId && question.solutions[i].answers[j].id === answer.answerText && question.solutions[i].answers[j].score > 0 && question.holes[k].selector) {
            numAnswersFound++
          }
        }
      }
    }

    if (numAnswersFound === question.solutions.length) {
      // all answers have been found
      feedbackState = this.FeedbackService.SOLUTION_FOUND
    } else if (numAnswersFound === question.solutions.length -1) {
      // one answer remains to be found
      feedbackState = this.FeedbackService.ONE_ANSWER_MISSING
    } else {
      // more answers remain to be found
      feedbackState = this.FeedbackService.MULTIPLE_ANSWERS_MISSING
    }
  }

  return feedbackState
}

/**
 * Get the correct answer from the solutions of a Question
 * @param   {Object} question
 * @returns {Array}
 */
ClozeQuestionService.prototype.getCorrectAnswer = function getCorrectAnswer(question) {
  var answer = []

  if (question.solutions) {
    for (var i = 0; i < question.holes.length; i++) {
      var hole = question.holes[i]

      // Get the correct answer
      var correct = this.getHoleCorrectAnswers(question, hole)
      if (correct) {
        for (var j = 0; j < correct.length; j++) {
          answer.push({
            holeId    : hole.id,
            answerText: hole.selector ? correct[j].id : correct[j].text
          })
        }
      }
    }
  }

  return answer
}

/**
 * Get the correct answer for a Hole
 * @param   {Object} question
 * @param   {Object} hole
 * @returns {Array}
 */
ClozeQuestionService.prototype.getHoleCorrectAnswers = function getHoleCorrectAnswers(question, hole) {
  var correctAnswers = []

  var solution = this.getHoleSolution(question, hole)
  if (solution) {
    // Get the correct answer
    for (var j = 0; j < solution.answers.length; j++) {
      if (solution.answers[j].score > 0) {
        correctAnswers.push(solution.answers[j])
      }
    }
  }

  return correctAnswers
}

/**
 * Get the answer of a specific Hole from the answer of the Question
 * @param {Array}  answer
 * @param {Object} hole
 * @returns {Object}
 */
ClozeQuestionService.prototype.getHoleAnswer = function getHoleAnswer(answer, hole) {
  var holeAnswer = null
  for (var i = 0; i < answer.length; i++) {
    if (hole.id === answer[i].holeId) {
      holeAnswer = answer[i]
      break // Stop searching
    }
  }

  return holeAnswer
}

/**
 * Get the complete solution for a Hole
 * @param   {Object} question
 * @param   {Object} hole
 * @returns {{
 *      id      : String
 *      answers : Array
 * }}
 */
ClozeQuestionService.prototype.getHoleSolution = function getHoleSolution(question, hole) {
  var solution = null
  if (question.solutions) {
    for (var i = 0; i < question.solutions.length; i++) {
      if (question.solutions[i].holeId == hole.id) {
        solution = question.solutions[i]
        break // Stop searching
      }
    }
  }

  return solution
}

ClozeQuestionService.prototype.getHoleStats = function (question, holeId) {
  var stats = null

  if (question.stats && question.stats.solutions) {
    for (var solution in question.stats.solutions) {
      if (question.stats.solutions.hasOwnProperty(solution)) {
        if (question.stats.solutions[solution].id === holeId) {
          stats = question.stats.solutions[solution]
        }
      }
    }
  }

  return stats
}

/**
 * Get the feedback for the Hole
 * @param   {Object} question
 * @param   {Object} hole
 * @param   {Object} answer
 * @returns {string}
 */
ClozeQuestionService.prototype.getHoleFeedback = function getHoleFeedback(question, hole, answer) {
  var feedback = ''

  var correct = this.getHoleCorrectAnswers(question, hole)
  if (correct) {
    for (var i = 0; i < correct.length; i++) {
      if (hole.selector && answer.answerText === correct[i].id && correct[i].feedback) {
        feedback = correct[i].feedback
      } else {
        if ((correct[i].caseSensitive && correct[i].text === answer.answerText)
            || (!correct[i].caseSensitive && correct[i].text.toLowerCase() === answer.answerText.toLowerCase())) {
          feedback = correct[i].feedback
        }
      }
    }
  }

  return feedback
}

export default ClozeQuestionService
