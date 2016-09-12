import AbstractQuestionService from './AbstractQuestionService'

/**
 * Choice Question Service
 * @param {FeedbackService} FeedbackService
 * @constructor
 */
function ChoiceQuestionService($log, FeedbackService) {
  AbstractQuestionService.call(this, $log, FeedbackService)
}

// Extends AbstractQuestionCtrl
ChoiceQuestionService.prototype = Object.create(AbstractQuestionService.prototype)

/**
 * Initialize the answer object for the Question
 */
ChoiceQuestionService.prototype.initAnswer = function initAnswer() {
  return []
}

/**
 * Check if all, all but one, or not all answers are found
 */
ChoiceQuestionService.prototype.answersAllFound = function answersAllFound(question, answer) {
  var feedbackState = -1

  if (question.solutions) {
    var numAnswersFound = 0
    var numSolutions = 0
    var uniqueSolutionFound = false

    if (answer) {
      for (var i=0; i<question.solutions.length; i++) {
        if (question.solutions[i].score > 0) {
          numSolutions++
        }
        for (var j=0; j<answer.length; j++) {
          if (question.solutions[i].id === answer[j] && question.solutions[i].score > 0) {
            if (question.solutions[i].rightResponse && !question.multiple) {
              uniqueSolutionFound = true
            }
            numAnswersFound++
          }
        }
      }
    }

    if (numAnswersFound === numSolutions || uniqueSolutionFound) {
            // all answers have been found
      feedbackState = this.FeedbackService.SOLUTION_FOUND
    } else if (numAnswersFound === numSolutions -1 && question.multiple) {
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
ChoiceQuestionService.prototype.getCorrectAnswer = function getCorrectAnswer(question) {
  var answer = []

  var betterFound = null
  if (question.solutions) {
    for (var i = 0; i < question.solutions.length; i++) {
      var choice = question.solutions[i]

      if (question.multiple) {
                // Multiple choices
        if (0 < choice.score) {
          answer.push(choice.id)
        }
      } else {
                // Unique choice
        if (choice.rightResponse) {
                    // Correct choice not already found OR current choice has more point than the previous found
          betterFound = choice
          break
        }
      }
    }
  }

  if (!question.multiple && betterFound) {
    answer.push(betterFound.id)
  }

  return answer
}

/**
 * Check whether a choice is part of the answer
 * @param   {Array}  answer
 * @param   {Object} choice
 * @returns {boolean}
 */
ChoiceQuestionService.prototype.isChoiceSelected = function isChoiceSelected(answer, choice) {
  return answer && -1 !== answer.indexOf(choice.id)
}

/**
 * Check if choice is valid or not
 * @param   {Object} question
 * @param   {Object} choice
 * @returns {boolean}
 */
ChoiceQuestionService.prototype.isChoiceValid = function isChoiceValid(question, choice) {
  var isValid = false

  var choiceSolution = this.getChoiceSolution(question, choice)
  if (choiceSolution.rightResponse) {
        // The current choice is part of the right response => User choice is Valid
    isValid = true
  }

  return isValid
}

/**
 * Get the solution for a choice
 * @param   {Object} question
 * @param   {Object} choice
 * @returns {Object}
 */
ChoiceQuestionService.prototype.getChoiceSolution = function getChoiceSolution(question, choice) {
  var solution = null

  if (question.solutions) {
        // Solutions have been loaded
    for (var i = 0; i < question.solutions.length; i++) {
      if (choice.id === question.solutions[i].id) {
        solution = question.solutions[i]
        break // Stop searching
      }
    }
  }

  return solution
}

/**
 * Get the Feedback of a Choice
 * @param   {Object} question
 * @param   {Object} choice
 * @returns {String}
 */
ChoiceQuestionService.prototype.getChoiceFeedback = function getChoiceFeedback(question, choice) {
  var feedback = ''

  var solution = this.getChoiceSolution(question, choice)
  if (solution) {
    feedback = solution.feedback
  }

  return feedback
}

/**
 * Get the Score of a Choice.
 *
 * @param   {Object} question
 * @param   {Object} choice
 * @returns {String}
 */
ChoiceQuestionService.prototype.getChoiceScore = function getChoiceScore(question, choice) {
  let score = 0

  var solution = this.getChoiceSolution(question, choice)
  if (solution) {
    score = solution.score
  }

  return score
}

ChoiceQuestionService.prototype.getChoiceStats = function getChoiceStats(question, choice) {
  var stats = null

  if (question.stats && question.stats.solutions) {
    for (var solution in question.stats.solutions) {
      if (question.stats.solutions.hasOwnProperty(solution)) {
        if (question.stats.solutions[solution].id == choice.id) {
          stats = question.stats.solutions[solution]
          break
        }
      }
    }

    if (!stats) {
            // No User have chosen this answer
      stats = {
        id: choice.id,
        count: 0
      }
    }
  }

  return stats
}

ChoiceQuestionService.prototype.getTotalScore = function (question) {
  let total = 0

  if ('fixed' === question.score.type) {
    total = question.score.success
  } else {
    let correct = this.getCorrectAnswer(question)
    for (let i = 0; i < correct.length; i++) {
      total += this.getChoiceScore(question, {id: correct[i]})
    }
  }

  return total
}

ChoiceQuestionService.prototype.getAnswerScore = function (question, answer) {
  let score = 0

  if (answer && 0 !== answer.length) {
    const foundSolutions = this.getFoundSolutions(question, answer)
    if (0 !== foundSolutions.length) {
      if ('fixed' === question.score.type) {
        // Check validitity of the answer
        let valid = true
        if (!question.multiple) {
          if (!foundSolutions[0].rightResponse) {
            valid = false
          }
        } else {
          if (question.solutions.length === foundSolutions.length) {
            // Correct number of solution found
            for (let i = 0; i < foundSolutions.length; i++) {
              if (!foundSolutions[i].rightResponse) {
                // Invalid answer in response
                valid = false
                break
              }
            }
          } else {
            // Missing solutions in answer
            valid = false
          }
        }

        score += valid ? question.score.success : question.score.failure
      } else {
        // Sum all solution score
        for (let i = 0; i < foundSolutions.length; i++) {
          score += foundSolutions[i].score
        }
      }
    }
  }

  if (0 > score) {
    score = 0
  }

  return score
}

ChoiceQuestionService.prototype.getFoundSolutions = function (question, answer) {
  let found = []
  if(question.solutions){
    for (let i = 0; i < question.solutions.length; i++) {
      for (let j = 0; j < answer.length; j++) {
        if (question.solutions[i].id === answer[j]) {
          found.push(question.solutions[i])
        }
      }
    }
  }

  return found
}

export default ChoiceQuestionService
