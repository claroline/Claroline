import {trans} from '#/main/app/intl/translation'

const SCORE_RULES = 'rules'

export default {
  name: SCORE_RULES,
  meta: {
    label: trans('score_rules', {}, 'quiz'),
    description: trans('score_rules_desc', {}, 'quiz')
  },

  configure: (score) => [
    {
      name: 'rules',
      label: trans('rules', {}, 'quiz'),
      type: 'score_rules',
      required: true
    }
  ],

  calculate: () => {

  }
}
