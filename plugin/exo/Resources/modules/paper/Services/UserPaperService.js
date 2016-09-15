
import angular from 'angular/index'

/**
 * UserPaper Service
 * Manages Paper of the current User
 */
export default class UserPaperService {
  /**
   * Constructor.
   *
   * @param {Object}          $http
   * @param {Object}          $q
   * @param {Object}          $filter
   * @param {PaperGenerator}    PaperGenerator
   * @param {PaperService}    PaperService
   * @param {ExerciseService} ExerciseService
   * @param {url}             url
   * @param {CorrectionMode}  CorrectionMode
   * @param {MarkMode}        MarkMode
   */
  constructor($http, $q, $filter, PaperGenerator, PaperService, ExerciseService, url, CorrectionMode, MarkMode) {
    this.$http = $http
    this.$q = $q
    this.$filter = $filter
    this.PaperGenerator = PaperGenerator
    this.PaperService = PaperService
    this.ExerciseService = ExerciseService
    this.UrlService = url
    this.CorrectionMode = CorrectionMode
    this.MarkMode = MarkMode

    /**
     * Current paper of the User.
     *
     * @type {Object}
     */
    this.paper = null

    /**
     * Number of papers already done by the User.
     *
     * @type {number}
     */
    this.nbPapers = 0
  }

  /**
   * Get Paper.
   *
   * @returns {Object}
   */
  getPaper() {
    return this.paper
  }

  /**
   * Set Paper.
   *
   * @param   {Object} paper
   *
   * @returns {UserPaperService}
   */
  setPaper(paper) {
    this.paper = paper

    return this
  }

  /**
   * Get Questions fot the current Paper.
   *
   * @returns {Object}
   */
  getQuestion() {
    return this.questions
  }

  /**
   * Set Questions.
   *
   * @param   {Array} questions
   *
   * @returns {UserPaperService}
   */
  setQuestions(questions) {
    this.questions = questions

    return this
  }

  /**
   * Get number of Papers.
   *
   * @returns {number}
   */
  getNbPapers() {
    return this.nbPapers
  }

  /**
   * Set number of Papers.
   *
   * @param {number} count
   *
   * @returns {UserPaperService}
   */
  setNbPapers(count) {
    this.nbPapers = count ? parseInt(count) : 0

    return this
  }

  /**
   * Get the index of a Step.
   *
   * @param   {Object} step
   *
   * @returns {Number}
   */
  getIndex(step) {
    let index = 0
    for (let i = 0; i < this.paper.order.length; i++) {
      if (this.paper.order[i].id === step.id) {
        index = i

        break
      }
    }

    return index
  }

  /**
   * Get the next Step as configured in the Paper of the User.
   *
   * @param   {Object} currentStep
   *
   * @returns {Object|null}
   */
  getNextStep(currentStep) {
    let nextId = null
    for (let i = 0; i < this.paper.order.length; i++) {
      if (this.paper.order[i].id === currentStep.id) {
        if (this.paper.order[i + 1]) {
          // There is a Step after the current one
          nextId = this.paper.order[i + 1].id
        }

        break
      }
    }

    return this.ExerciseService.getStep(nextId)
  }

  /**
   * Get the previous Step as configured in the Paper of the User.
   *
   * @param   {Object} currentStep
   *
   * @returns {Object|null}
   */
  getPreviousStep(currentStep) {
    let previousId = null
    for (let i = 0; i < this.paper.order.length; i++) {
      if (this.paper.order[i].id === currentStep.id) {
        if (this.paper.order[i - 1]) {
          // There is a Step after the current one
          previousId = this.paper.order[i - 1].id
        }

        break
      }
    }

    return this.ExerciseService.getStep(previousId)
  }

  /**
   * Order the Questions of a Step.
   *
   * @param   {Object} step
   *
   * @returns {Array} The ordered list of Questions
   */
  orderStepQuestions(step) {
    return this.PaperService.orderStepQuestions(this.paper, step, this.questions)
  }

  /**
   * Get Paper for a Question.
   *
   * @param {Object} question
   */
  getQuestionPaper(question) {
    return this.PaperService.getQuestionPaper(this.paper, question)
  }

  /**
   * Start the Exercise.
   *
   * @param   {Object} exercise
   *
   * @returns {promise}
   */
  start(exercise) {
    const deferred = this.$q.defer()

    if (!this.paper || this.paper.end) {
      // Start a new Paper (or load an interrupted one)
      if (!this.PaperService.isNoSaveMode()) {
        // Submit the attempt to the server
        this.$http
          .post(this.UrlService('exercise_new_attempt', {id: exercise.id}))
          .success(response => {
            this.paper = response.paper
            this.questions = response.questions
            deferred.resolve(response)
          })
          .error(() => {
            deferred.reject({})
          })
      } else {
        this.paper = this.PaperGenerator.generate(exercise, 'anonymous', this.paper)
        this.questions = this.PaperService.getPaperQuestions(this.paper)

        deferred.resolve({
          paper: this.paper,
          questions: this.questions
        })
      }
    } else {
      // Continue the current Paper
      deferred.resolve({
        paper: this.paper,
        questions: this.questions
      })
    }

    return deferred.promise
  }

  /**
   * Interrupt the current Paper
   */
  interrupt() {
    this.paper.interrupted = true
  }

  /**
   * Submit a Step answers to the server.
   *
   * @param {Object} step
   */
  submitStep(step) {
    const deferred = this.$q.defer()

    const stepPapers = []
    for (let i = 0; i < step.items.length; i++) {
      let item = step.items[i]
      let itemPaper = this.getQuestionPaper(item)

      // Update nbTries
      itemPaper.nbTries++

      stepPapers.push(itemPaper)
    }

    if (!this.PaperService.isNoSaveMode()) {
      // Get answers for each Question of the Step
      const stepAnswers = {}
      for (let i = 0; i < stepPapers.length; i++) {
        stepAnswers[stepPapers[i].id] = stepPapers[i].answer ? stepPapers[i].answer : ''
      }

      // There are answers to post
      this.$http
        .put(
          this.UrlService('exercise_submit_step', {paperId: this.paper.id, stepId: step.id}),
          {data: stepAnswers}
        )
        .success(response => {
          if (response) {
            for (let i = 0; i < response.length; i++) {
              if (response[i]) {
                let item = null

                // Get item in Step
                for (let j = 0; j < step.items.length; j++) {
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
                  const paper = this.getQuestionPaper(item)
                  paper.score = response[i].answer.score
                  paper.nbTries = response[i].answer.nbTries
                }
              }
            }
          }

          deferred.resolve(step)
        })
        .error(() => {
          for (let i = 0; i < stepPapers.length; i++) {
            stepPapers[i].nbTries -= 1
          }

          deferred.reject([])
        })
    } else {
      const exercise = this.ExerciseService.getExercise()
      if (this.ExerciseService.TYPE_FORMATIVE === exercise.meta.type) {
        // Directly calculate score for submitted questions
        for (let i = 0; i < stepPapers.length; i++) {
          this.PaperService.calculateQuestionScore(stepPapers[i])
        }
      }

      deferred.resolve(step)
    }

    return deferred.promise
  }

  /**
   * End the Exercise.
   *
   * @returns {promise}
   */
  end() {
    const deferred = this.$q.defer()

    // Set end of the paper
    this.paper.end = this.$filter('date')(new Date(), 'yyyy-MM-dd\'T\'HH:mm:ss')
    this.paper.interrupted = false

    // Update the number of finished papers
    this.nbPapers++

    if (!this.PaperService.isNoSaveMode()) {
      this.$http
        .put(this.UrlService('exercise_finish_paper', {id: this.paper.id}))
        .success(response => {
          // Update the current User Paper with updated data (endDate particularly)
          angular.merge(this.paper, response)

          deferred.resolve(response)
        })
        .error(() => {
          this.nbPapers--
          deferred.reject({})
        })
    } else {
      if (this.isScoreAvailable(this.paper)) {
        this.PaperService.calculateScore(this.paper)
      }

      // Set paper for correction display
      this.PaperService.setCurrent({
        paper: this.paper,
        questions: this.questions
      })

      deferred.resolve(this.paper)
    }

    return deferred.promise
  }

  isNoSaveMode() {
    return this.PaperService.isNoSaveMode()
  }

  /**
   * Check if the User is allowed to compose (max attempts of the Exercise is not reached).
   *
   * @returns {boolean}
   */
  isAllowedToCompose() {
    const exercise = this.ExerciseService.getExercise()

    let allowed = true
    if (exercise.meta.maxAttempts && this.nbPapers >= exercise.meta.maxAttempts) {
      // Max attempts reached => user can not do the exercise
      allowed = false
    }

    return allowed
  }

  /**
   * Check if the correction of the Exercise is available.
   *
   * @param {Object} paper
   *
   * @returns {Boolean}
   */
  isCorrectionAvailable(paper) {
    let available = false

    if (this.ExerciseService.isEditEnabled()) {
      // Always show correction for exercise's administrators
      available = true
    } else {
      // Use the configuration of the Exercise to know if it's available
      const exercise = this.ExerciseService.getExercise()

      switch (exercise.meta.correctionMode) {
        case this.CorrectionMode.AFTER_END: {
          available = null !== paper.end
          break
        }

        case this.CorrectionMode.AFTER_LAST_ATTEMPT: {
          available = (0 === exercise.meta.maxAttempts || this.nbPapers >= exercise.meta.maxAttempts)
          break
        }

        case this.CorrectionMode.AFTER_DATE: {
          const now = new Date()

          let correctionDate = null
          if (null !== exercise.meta.correctionDate) {
            correctionDate = new Date(Date.parse(exercise.meta.correctionDate))
          }

          available = (null === correctionDate || now >= correctionDate)
          break
        }

        default:
        case this.CorrectionMode.NEVER: {
          available = false
          break
        }
      }
    }

    return available
  }

  /**
   * Check if the score obtained by the User for the Exercise is available.
   *
   * @param {Object} paper
   *
   * @returns {Boolean}
   */
  isScoreAvailable(paper) {
    let available = false

    if (this.ExerciseService.isEditEnabled()) {
      // Always show score for exercise's administrators
      available = true
    } else {
      // Use the configuration of the Exercise to know if it's available
      const exercise = this.ExerciseService.getExercise()

      switch (exercise.meta.markMode) {
        case this.MarkMode.WITH_CORRECTION:
          available = this.isCorrectionAvailable(paper)
          break

        case this.MarkMode.AFTER_END:
          available = null !== paper.end
          break

        case this.MarkMode.NEVER:
          available = false
          break

        // Show score if nothing specified
        default:
          available = false
          break
      }
    }

    return available
  }
}
