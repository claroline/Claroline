import AbstractQuestionCtrl from './AbstractQuestionCtrl'

/**
 * Graphic Question Controller
 * @param {FeedbackService}        FeedbackService
 * @param {GraphicQuestionService} GraphicQuestionService
 * @param {ImageAreaService}       ImageAreaService
 * @constructor
 */
function GraphicQuestionCtrl(FeedbackService, GraphicQuestionService, ImageAreaService) {
  AbstractQuestionCtrl.apply(this, arguments)

  this.GraphicQuestionService = GraphicQuestionService
  this.ImageAreaService = ImageAreaService
}

// Extends AbstractQuestionCtrl
GraphicQuestionCtrl.prototype = Object.create(AbstractQuestionCtrl.prototype)

/**
 * The current image of the question
 * @type {object}
 */
GraphicQuestionCtrl.prototype.$image = null

/**
 * Are the correction for the Question displayed ?
 * @type {boolean}
 */
GraphicQuestionCtrl.prototype.includeCorrection = false

/**
 * Reset answer
 */
GraphicQuestionCtrl.prototype.reset = function reset() {
  this.answer.splice(0, this.answer.length)
}

GraphicQuestionCtrl.prototype.isPointerValid = function isPointerValid(pointer) {
  var valid = undefined

  if (this.question.solutions && this.feedback.visible) {
    valid = false
    for (var i = 0; i < this.question.solutions.length; i++) {
      valid = this.ImageAreaService.isInArea(this.question.solutions[i], pointer)
      if (valid) {
        break
      }
    }
  }

  return valid
}

GraphicQuestionCtrl.prototype.areaHasPointer = function (area) {
  var hasPointer = false
  for (var i = 0; i < this.answer.length; i++) {
    if (this.ImageAreaService.isInArea(area, this.answer[i])) {
      hasPointer = true
    }
  }

  return hasPointer
}

/**
 *
 */
GraphicQuestionCtrl.prototype.onFeedbackShow = function onFeedbackShow() {
  this.solutionsFound = []
  for (var i = 0; i < this.question.solutions.length; i++) {
    if (this.areaHasPointer(this.question.solutions[i])) {
      this.solutionsFound.push(this.question.solutions[i])
    }
  }
}

/**
 *
 */
GraphicQuestionCtrl.prototype.onFeedbackHide = function onFeedbackHide() {
    // Reset validation
  for (var i = 0; i < this.answer.length; i++) {
        // Keep track on answer found by user (only errors are reset)
    this.answer.$invalid = false
  }
}

export default GraphicQuestionCtrl
