import {trans} from '#/main/app/intl/translation'

const SCORE_MANUAL = 'manual'

export default {
  name: SCORE_MANUAL,
  meta: {
    label: trans('score_manual', {}, 'quiz'),
    description: trans('score_manual_desc', {}, 'quiz')
  },

  configure: (score) => [
    {
      name: 'max',
      label: trans('score_max', {}, 'quiz'),
      type: 'number',
      required: true,
      options: {
        min: 0
      }
    }
  ],

  // type requires manual correction
  calculate: () => null
}
