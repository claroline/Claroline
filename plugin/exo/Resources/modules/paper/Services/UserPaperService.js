/**
 * UserPaper Service
 * Manages Paper of the current User
 * @param {Object}          $http
 * @param {Object}          $q
 * @param {PaperService}    PaperService
 * @param {ExerciseService} ExerciseService
 * @param {url}             url
 * @constructor
 */
function UserPaperService($http, $q, PaperService, ExerciseService, url) {
  this.$http = $http
  this.$q = $q
  this.PaperService = PaperService
  this.ExerciseService = ExerciseService
  this.UrlService = url
}

/**
 * Current paper of the User
 * @type {Object}
 */
UserPaperService.prototype.paper = null

/**
 * Number of papers already done by the User
 * @type {number}
 */
UserPaperService.prototype.nbPapers = 0

/**
 * Get Paper
 * @returns {Object}
 */
UserPaperService.prototype.getPaper = function getPaper() {
  return this.paper
}

/**
 * Set Paper
 * @param   {Object} paper
 * @returns {UserPaperService}
 */
UserPaperService.prototype.setPaper = function setPaper(paper) {
  this.paper = paper

  return this
}

/**
 * Get number of Papers
 * @returns {number}
 */
UserPaperService.prototype.getNbPapers = function getNbPapers() {
  return this.nbPapers
}

/**
 * Set number of Papers
 * @param {number} count
 * @returns {UserPaperService}
 */
UserPaperService.prototype.setNbPapers = function setNbPapers(count) {
  this.nbPapers = count ? parseInt(count) : 0

  return this
}

/**
 * Get the index of a Step
 * @param   {Object} step
 * @returns {Number}
 */
UserPaperService.prototype.getIndex = function getIndex(step) {
  var index = 0

  for (var i = 0; i < this.paper.order.length; i++) {
    if (this.paper.order[i].id === step.id) {
      index = i

      break
    }
  }

  return index
}

/**
 * Get the next Step has configured in the Paper of the User
 * @param   {Object} currentStep
 * @returns {Number}
 */
UserPaperService.prototype.getNextStepId = function getNextStepId(currentStep) {
  var nextStep = null
  for (var i = 0; i < this.paper.order.length; i++) {
    if (this.paper.order[i].id === currentStep.id) {
      if (this.paper.order[i + 1]) {
        // There is a Step after the current one
        nextStep = this.paper.order[i + 1].id
      }

      break
    }
  }

  return nextStep
}

/**
 * Get the previous Step has configured in the Paper of the User
 * @param   {Object} currentStep
 * @returns {Number}
 */
UserPaperService.prototype.getPreviousStepId = function getPreviousStepId(currentStep) {
  var previousStep = null
  for (var i = 0; i < this.paper.order.length; i++) {
    if (this.paper.order[i].id === currentStep.id) {
      if (this.paper.order[i - 1]) {
        // There is a Step after the current one
        previousStep = this.paper.order[i - 1].id
      }

      break
    }
  }

  return previousStep
}

/**
 * Order the Questions of a Step
 * @param   {Object} step
 * @returns {Array} The ordered list of Questions
 */
UserPaperService.prototype.orderStepQuestions = function orderStepQuestions(step) {
  return this.PaperService.orderStepQuestions(this.paper, step)
}

/**
 * Get Paper for a Question
 * @param {Object} question
 */
UserPaperService.prototype.getQuestionPaper = function getQuestionPaper(question) {
  return this.PaperService.getQuestionPaper(this.paper, question)
}

/**
 * Start the Exercise
 * @param   {Object} exercise
 * @returns {promise}
 */
UserPaperService.prototype.start = function start(exercise) {
  var deferred = this.$q.defer()

  if (!this.paper || this.paper.end) {
    // Start a new Paper (or load an interrupted one)
    this.$http.post(
      this.UrlService('exercise_new_attempt', {id: exercise.id})
      ).success(function (response) {
        this.paper = response
        deferred.resolve(this.paper)
      }.bind(this)).error(function () {
        // TODO : display message

        deferred.reject([])
      })
  } else {
    // Continue the current Paper
    deferred.resolve(this.paper)
  }

  return deferred.promise
}

/**
 * End the Exercise
 * @returns {promise}
 */
UserPaperService.prototype.end = function end() {
  var deferred = this.$q.defer()

  this.$http
          .put(
            this.UrlService('exercise_finish_paper', {id: this.paper.id})
            )

          // Success callback
          .success(function onSuccess(response) {
            // Update the number of finished papers
            this.nbPapers++

            // Update the current User Paper with updated data (endDate particularly)
            angular.merge(this.paper, response)

            deferred.resolve(response)
          }.bind(this))

          // Error callback
          .error(function onError() {
            // TODO : display message

            deferred.reject([])
            /*var url = Routing.generate('ujm_sequence_error', {message: msg, code: code});*/
            /*$window.location = url;*/
          })

  return deferred.promise
}

/**
 * Use an hint
 * @returns {promise}
 */
UserPaperService.prototype.useHint = function useHint(question, hint) {
  var deferred = this.$q.defer()
  this.$http
          .get(
            this.UrlService('exercise_hint', {paperId: this.paper.id, hintId: hint.id})
            )
          .success(function onSuccess(response) {
            // Update question Paper with used hint
            var questionPaper = this.getQuestionPaper(question)

            questionPaper.hints.push({
              id: hint.id,
              penalty: hint.penalty,
              value: response
            })

            deferred.resolve(response)
          }.bind(this))
          .error(function onError() {
            deferred.reject([])
            /*var url = Routing.generate('ujm_sequence_error', {message:msg, code:code});*/
            /*$window.location = url;*/
          })

  return deferred.promise
}

/**
 * Submit Step answers to the server
 * @param {Object} step
 */
UserPaperService.prototype.submitStep = function submitStep(step) {
  var deferred = this.$q.defer()

  // Get answers for each Question of the Step
  var stepAnswers = {}
  if (step && step.items) {
    for (var i = 0; i < step.items.length; i++) {
      var item = step.items[i]
      var itemPaper = this.getQuestionPaper(item)

      if (itemPaper && itemPaper.answer) {
        stepAnswers[item.id] = itemPaper.answer
      } else {
        stepAnswers[item.id] = ''
      }
    }
  }

  // There are answers to post
  this.$http
          .put(
            this.UrlService('exercise_submit_step', {paperId: this.paper.id, stepId: step.id}),
            {data: stepAnswers}
          )

          // Success callback
          .success(function onSuccess(response) {
            if (response) {
              for (var i = 0; i < response.length; i++) {
                if (response[i]) {
                  var item = null

                  // Get item in Step
                  for (var j = 0; j < step.items.length; j++) {
                    if (response[i].question.id === step.items[j].id) {
                      item = step.items[j]
                      break // Stop searching
                    }
                  }

                  if (item) {
                    // Update question with solutions and feedback
                    item.solutions = response[i].question.solutions ? response[i].question.solutions : []
                    item.feedback = response[i].question.feedback ? response[i].question.feedback : null

                    // Update paper with Score
                    var paper = this.getQuestionPaper(item)
                    paper.score = response[i].answer.score
                    paper.nbTries = response[i].answer.nbTries
                  }
                }
              }
            }

            deferred.resolve(response)
          }.bind(this))

          // Error callback
          .error(function onError() {
            // TODO : display message

            deferred.reject([])
            /*var url = Routing.generate('ujm_sequence_error', { message: msg, code: code });*/
            //$window.location = url;
          })

  return deferred.promise
}

/**
 * Check if the User is allowed to compose (max attempts of the Exercise is not reached)
 * @returns {boolean}
 */
UserPaperService.prototype.isAllowedToCompose = function isAllowedToCompose() {
  var allowed = true

  var exercise = this.ExerciseService.getExercise()
  if (exercise.meta.maxAttempts && this.nbPapers >= exercise.meta.maxAttempts) {
    // Max attempts reached => user can not do the exercise
    allowed = false
  }

  return allowed
}

/**
 * Check if the correction of the Exercise is available
 * @param {Object} paper
 * @returns {Boolean}
 */
UserPaperService.prototype.isCorrectionAvailable = function isCorrectionAvailable(paper) {
  var available = false

  if (this.ExerciseService.isEditEnabled()) {
    // Always show correction for exercise's administrators
    available = true
  } else {
    // Use the configuration of the Exercise to know if it's available
    var exercise = this.ExerciseService.getExercise()

    switch (exercise.meta.correctionMode) {
    // At the end of assessment
    case '1':
      available = null !== paper.end
      break

    // After the last attempt
    case '2':
      available = (0 === exercise.meta.maxAttempts || this.nbPapers >= exercise.meta.maxAttempts)
      break

    // From a fixed date
    case '3':
      var now = new Date()

      var correctionDate = null
      if (null !== exercise.meta.correctionDate) {
        correctionDate = new Date(Date.parse(exercise.meta.correctionDate))
      }

      available = (null === correctionDate || now >= correctionDate)
      break

      // Never
    default:
    case '4':
      available = false
      break
    }
  }

  return available
}

/**
 * Check if the score obtained by the User for the Exercise is available
 * @param {Object} paper
 * @returns {Boolean}
 */
UserPaperService.prototype.isScoreAvailable = function isScoreAvailable(paper) {
  var available = false

  if (this.ExerciseService.isEditEnabled()) {
    // Always show score for exercise's administrators
    available = true
  } else {
    // Use the configuration of the Exercise to know if it's available
    var exercise = this.ExerciseService.getExercise()

    switch (exercise.meta.markMode) {
    // At the same time that the correction
    case '1':
      available = this.isCorrectionAvailable(paper)
      break

    // At the end of the assessment
    case '2':
      available = null !== paper.end
      break

    // Show score if nothing specified
    default:
      available = false
      break
    }
  }

  return available
}

import angular from 'angular/index'

export default UserPaperService
