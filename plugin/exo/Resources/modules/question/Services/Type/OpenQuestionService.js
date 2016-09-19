import AbstractQuestionService from './AbstractQuestionService'

/**
 * Open Question Service
 * @param {FeedbackService}  FeedbackService
 * @constructor
 */
function OpenQuestionService($log, FeedbackService) {
  AbstractQuestionService.call(this, $log, FeedbackService)
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

  if (question.solutions) {
    if ('long' !== question.typeOpen) {
      // Search used keywords in student answer
      const foundKeywords = this.getFoundSolutions(question, answer)

      if (0 !== foundKeywords.length) {
        if ('oneWord' === question.typeOpen) {
          if (foundKeywords[0].score > 0) {
            feedbackState = this.FeedbackService.SOLUTION_FOUND
          } else {
            feedbackState = this.FeedbackService.ONE_ANSWER_MISSING
          }
        } else {
          // Short question
          if (question.solutions.length === foundKeywords.length) {
            feedbackState = this.FeedbackService.SOLUTION_FOUND
          } else if (question.solutions.length - 1 === foundKeywords.length) {
            feedbackState = this.FeedbackService.ONE_ANSWER_MISSING
          }
        }
      } else {
        feedbackState = this.FeedbackService.MULTIPLE_ANSWERS_MISSING
      }
    } else {
      feedbackState = this.FeedbackService.SOLUTION_FOUND
    }
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
      let betterFound = null
      for (let i = 0; i < question.solutions.length; i++) {
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

OpenQuestionService.prototype.getTotalScore = function (question) {
  let total = 0

  switch (question.typeOpen) {
    case 'long': {
      total = question.score.success
      break
    }
    case 'oneWord': {
      let maxScore = 0

      if (question.solutions) {
        for (let i = 0; i < question.solutions.length; i++) {
          if (question.solutions[i].score > maxScore) {
            maxScore = question.solutions[i].score
          }
        }
      }

      total = maxScore
      break
    }
    case 'short': {
      if (question.solutions) {
        for (let i = 0; i < question.solutions.length; i++) {
          total += question.solutions[i].score
        }
      }
      break
    }
  }

  return total
}

/**
 *
 * @returns {number}
 */
OpenQuestionService.prototype.getAnswerScore = function (question, answer) {
  let score = null

  if ('long' !== question.typeOpen) {
    const foundKeywords = this.getFoundSolutions(question, answer)

    score = 0
    if (foundKeywords.length !== 0) {
      if ('oneWord' === question.typeOpen) {
        // Give points for the first found
        score += foundKeywords[0].score
      } else if ('short' === question.typeOpen) {
        // Give points for all the keywords
        for (let i = 0; i < foundKeywords.length; i++) {
          score += foundKeywords[i].score
        }
      }
    }

    if (0 > score) {
      score = 0
    }
  } else {
    // Open questions need to be manually corrected
    score = -1
  }

  return score
}

OpenQuestionService.prototype.getFoundSolutions = function (question, answer) {
  const found = []

  if (answer && question.solutions) {
    // Search used keywords in student answer
    for (var i = 0; i < question.solutions.length; i++) {
      let solution = question.solutions[i]

      // Check in answer if the keyword as been used
      const searchFlags      = 'g' + (solution.caseSensitive ? 'i' : '')
      const searchExpression = new RegExp('\\b' + solution.word + '\\b', searchFlags)
      if (-1 !== answer.search(searchExpression)) {
        // Keyword has been found in answer => Update formatted answer
        found.push(solution)
      }
    }
  }

  return found
}

export default OpenQuestionService
