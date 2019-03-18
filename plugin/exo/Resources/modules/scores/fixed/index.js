import {trans} from '#/main/app/intl/translation'

const SCORE_FIXED  = 'fixed'

export default {
  name: SCORE_FIXED,
  meta: {
    label: trans('score_fixed', {}, 'quiz'),
    description: trans('score_fixed_desc', {}, 'quiz')
  },

  configure: (score) => [
    {
      name: 'success',
      label: trans('score_fixed_success', {}, 'quiz'),
      help: trans('score_fixed_success_help', {}, 'quiz'),
      type: 'number',
      required: true,
      options: {
        min: 0 || score.success
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

  calculate: (item) => {

  }
}
