import {trans} from '#/main/app/intl/translation'

const SCORE_SUM = 'sum'

export default {
  name: SCORE_SUM,
  meta: {
    label: trans('score_sum', {}, 'quiz'),
    description: trans('score_sum_desc', {}, 'quiz')
  },

  // no additional configuration
  configure: () => [],

  calculate: (item) => {

  }
}
