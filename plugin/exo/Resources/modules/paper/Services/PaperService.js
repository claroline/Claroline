
import angular from 'angular/index'

/**
 * PaperService
 */
export default class PaperService {
  /**
   * Constructor.
   *
   * @param {Object}           $http
   * @param {Object}           $q
   * @param {ExerciseService}  ExerciseService
   * @param {StepService}      StepService
   * @param {QuestionService}  QuestionService
   * @param {Function}         url
   */
  constructor($http, $q, ExerciseService, StepService, QuestionService, url) {
    this.$http           = $http
    this.$q              = $q
    this.ExerciseService = ExerciseService
    this.StepService     = StepService
    this.QuestionService = QuestionService
    this.UrlService = url

    /**
     * Contains the Paper to display.
     *
     * @type {{paper: object, questions: object}}
     */
    this.current = null

    /**
     * Number of papers already done for the current Exercise.
     *
     * @type {number}
     */
    this.nbPapers = 0

    /**
     * Disable sending papers to server.
     *
     * @type {boolean}
     */
    this.noSaveMode = false
  }

  /**
   * Is server save enabled ?
   *
   * @returns {boolean}
   */
  isNoSaveMode() {
    return this.noSaveMode
  }

  /**
   * Disable / Enable server save
   *
   * @param {boolean} noSaveMode
   */
  setNoSaveMode(noSaveMode) {
    this.noSaveMode = noSaveMode

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
   * @returns {PaperService}
   */
  setNbPapers(count) {
    this.nbPapers = count ? parseInt(count) : 0

    return this
  }

  /**
   * Get the paper to display.
   * (if we don't change current paper, it's loaded from memory, else we call the server to load it)
   *
   * @param   {String} id
   *
   * @returns {Promise}
   */
  getCurrent(id) {
    const deferred = this.$q.defer()

    if (!this.current || !this.current.paper || id !== this.current.paper.id) {
      // We need to load the paper from the server
      this.$http
        .get(this.UrlService('exercise_export_paper', { id: id }))
        .success(response => {
          this.current = response

          deferred.resolve(this.current)
        })
        .error(() => {
          deferred.reject([])
        })
    } else {
      // Send the current loaded paper
      deferred.resolve(this.current)
    }

    return deferred.promise
  }

  /**
   * Manually set the current paper.
   */
  setCurrent(current) {
    this.current = current
  }

  /**
   * Get all papers for an Exercise.
   *
   * @returns {Promise}
   */
  getAll() {
    const exercise = this.ExerciseService.getExercise()
    const deferred = this.$q.defer()

    this.$http
      .get(this.UrlService('exercise_papers', { id: exercise.id }))
      .success(response => {
        this.setNbPapers(response.length)

        deferred.resolve(response)
      })
      .error(() => {
        deferred.reject([])
      })

    return deferred.promise
  }

  /**
   * Get Paper for a Question.
   *
   * @param {Object} paper
   * @param {Object} question
   */
  getQuestionPaper(paper, question) {
    let questionPaper = null

    for (let i = 0; i < paper.questions.length; i++) {
      if (paper.questions[i].id === question.id) {
        // Question paper found
        questionPaper = paper.questions[i]
        break
      }
    }

    if (null === questionPaper) {
      // There is no Paper for the current Question => initialize Object properties
      questionPaper = {
        id     : question.id,
        answer : null,
        score  : 0,
        nbTries: 0,
        hints  : []
      }

      paper.questions.push(questionPaper)
    }

    return questionPaper
  }

  /**
   * Delete all papers of an Exercise
   */
  deleteAll(papers) {
    const exercise = this.ExerciseService.getExercise()
    const deferred = this.$q.defer()

    this.$http
      .delete(this.UrlService('ujm_exercise_delete_papers', { id: exercise.id }))
      .success(response => {
        papers.splice(0, papers.length) // Empty the Papers list

        this.setNbPapers(0)

        deferred.resolve(response)
      })
      .error(() => {
        deferred.reject([])
      })

    return deferred.promise
  }

  /**
   * Check whether a paper need manual correction.
   *
   * @param {Object} paper
   *
   * @returns {Boolean}
   */
  needManualCorrection(paper) {
    var needed = false
    if (paper.questions && 0 !== paper.questions.length) {
      for(let i = 0; i < paper.questions.length; i++){
        if (-1 === paper.questions[i].score) {
          // The question has not been marked
          needed = true
          break // Stop searching
        }
      }
    }

    return needed
  }

  /**
   * Save the score for a question
   */
  saveScore(question, score) {
    const deferred = this.$q.defer()
    this.$http
      .put(this.UrlService('exercise_save_score', { id: this.current.paper.id, questionId: question.id, score: score }))
      .success((response) => {
        // Update paper instance
        angular.merge(this.current.paper, response)

        deferred.resolve(response)
      })
      .error(() => {
        deferred.reject([])
      })

    return deferred.promise
  }

  /**
   * Get the Question of a Paper from the Exercise.
   *
   * @param paper
   * @return {array}
   */
  getPaperQuestions(paper) {
    const questions = []

    for (let i = 0; i < paper.order.length; i++) {
      let step = paper.order[i]
      for (let j = 0; j < step.items.length; j++) {
        let item = this.ExerciseService.getItem(step.items[j])
        if (item) {
          questions.push(item)
        }
      }
    }

    return questions
  }

  /**
   * Calculate the score of the Paper (/20)
   * @param   {Object} paper
   * @param   {Array} questions
   * @returns {number}
   */
  getPaperScore(paper, questions) {
    let score = 0.0

    if (null === paper.score) {
      this.calculateScore(paper)
    }

    if (paper.score) {
      let scoreTotal = 0
      for (let i = 0; i < questions.length; i++) {
        scoreTotal += this.QuestionService.calculateTotal(questions[i])
      }

      score = paper.score * 20 / scoreTotal
      if (score > 0) {
        score = Math.round(score / 0.5) * 0.5
      } else {
        score = 0
      }
    }

    return score
  }

  calculateScore(paper) {
    paper.score = 0
    for (let i = 0; i < paper.questions.length; i++) {
      paper.score += this.calculateQuestionScore(paper.questions[i])
    }

    return paper.score
  }

  calculateQuestionScore(questionPaper) {
    let item = this.ExerciseService.getItem(questionPaper.id)
    if (item) {
      this.QuestionService.calculateScore(item, questionPaper)
    }

    return questionPaper.score
  }

  /**
   * Get the Questions of a Step.
   *
   * @param   {Object} paper
   * @param   {Object} step
   * @param   {array}  questions
   *
   * @returns {array} The ordered list of Questions
   */
  orderStepQuestions(paper, step, questions) {
    let ordered = []
    if (step.items && 0 !== step.items.length) {
      // Get order for the current Step
      const stepPaper = paper.order.find(current => current.id === step.id)

      if (stepPaper) {
        for (let i = 0; i < stepPaper.items.length; i++) {
          let question = questions.find(question => question.id === stepPaper.items[i])
          if (question) {
            ordered.push(question)
          }
        }
      }
    }

    return ordered
  }

  /**
   * Get Exercise Metadata
   */
  getExerciseMeta() {
    const exercise = this.ExerciseService.getExercise()

    return exercise.meta
  }

  /**
   * Get paper steps depending on exercise configuration (pick some step among all, random step order and so on)
   */
  getPaperSteps(){
    // get all steps (with all meta data)
    const steps = this.ExerciseService.getExercise().steps
    // get order and steps for the paper
    const order = this.current.paper.order
    let orderedSteps = []
    if(undefined !== order && null !== order){
      for(const ordered of order){
        const current = steps.find(step => step.id === ordered.id)
        if(undefined !== current){
          orderedSteps.push(current)
        }
      }
    }
    return orderedSteps
  }
}
