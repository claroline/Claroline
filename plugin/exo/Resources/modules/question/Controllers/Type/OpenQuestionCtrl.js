import AbstractQuestionCtrl from './AbstractQuestionCtrl'

/**
 * Open Question Controller
 * @param {FeedbackService} FeedbackService
 * @param {OpenQuestionService} OpenQuestionService
 * @constructor
 */
function OpenQuestionCtrl(FeedbackService, OpenQuestionService) {
  AbstractQuestionCtrl.apply(this, arguments)

  this.OpenQuestionService = OpenQuestionService
}

// Extends AbstractQuestionCtrl
OpenQuestionCtrl.prototype = Object.create(AbstractQuestionCtrl.prototype)

/**
 * Answer of the student with highlighted keywords
 * @type {string}
 */
OpenQuestionCtrl.prototype.answerWithKeywords = ''

/**
 * Callback executed when Feedback for the Question is shown
 */
OpenQuestionCtrl.prototype.onFeedbackShow = function onFeedbackShow() {
  if (this.question.solutions) {
    this.answerWithKeywords = this.answer ? this.answer : ''

    // Get EOL
    this.answerWithKeywords = this.answerWithKeywords.replace(/(\r\n|\n|\r)/gm, '<br/>')

    if ('long' !== this.question.typeOpen) {
      // Initialize answer with keywords
      // Search used keywords in student answer
      const foundKeywords = this.OpenQuestionService.getFoundSolutions(this.question, this.answer)
      for (var i = 0; i < foundKeywords.length; i++) {
        // Check in answer if the keyword as been used
        const searchFlags = 'g' + (foundKeywords[i].caseSensitive ? 'i' : '')
        const searchExpression = new RegExp('\\b' + foundKeywords[i].word + '\\b', searchFlags)

        let keyword = ''
        keyword += '<b class="text-success feedback-info" data-toggle="tooltip" title="' + (foundKeywords[i].feedback || '') + '">'
        keyword += foundKeywords[i].word
        keyword += '<span class="fa fa-fw fa-check"></span>'
        keyword += '</b>'

        this.answerWithKeywords = this.answerWithKeywords.replace(searchExpression, keyword, searchFlags)

        if ('oneWord' === this.question.typeOpen) {
          // For one word question we give points only for the first typed word
          break
        }
      }
    }
  }
}

export default OpenQuestionCtrl
