import {trans} from '#/main/app/intl/translation'

const SCORE_FIXED  = 'fixed'

/**
 * Fixed score.
 *
 * Let's the user define a success and a failure score.
 * If the user has selected all the correct answers and no incorrect,
 * he will get the success score, otherwise he'll get the failure one.
 */
export default {
  name: SCORE_FIXED,
  meta: {
    label: trans('score_fixed', {}, 'quiz'),
    description: trans('score_fixed_desc', {}, 'quiz')
  },

  hasAnswerScores: false,

  configure: (score) => [
    {
      name: 'success',
      label: trans('score_fixed_success', {}, 'quiz'),
      help: trans('score_fixed_success_help', {}, 'quiz'),
      type: 'number',
      required: true,
      options: {
        min: 0 || score.failure
      }
    }, {
      name: 'failure',
      label: trans('score_fixed_failure', {}, 'quiz'),
      help: trans('score_fixed_failure_help', {}, 'quiz'),
      type: 'number',
      required: true,
      options: {
        max: score.success
      }
    }
  ],

  /**
   *
   * @param {object} scoreRule
   * @param {object} correctedAnswer
   *
   * @return {number}
   */
  calculate: (scoreRule, correctedAnswer) => {
    if (correctedAnswer.getMissing().length > 0 || correctedAnswer.getUnexpected().length > 0) {
      return scoreRule.failure
    }

    return scoreRule.success
  },

  calculateTotal: (scoreRule) => scoreRule.success
}
