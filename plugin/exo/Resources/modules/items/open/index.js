import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'

import {CorrectedAnswer} from '#/plugin/exo/quiz/correction/components/corrected-answer'

import {OpenItem} from '#/plugin/exo/items/open/prop-types'

// components
import {OpenPaper} from '#/plugin/exo/items/open/components/paper'
import {OpenPlayer} from '#/plugin/exo/items/open/components/player'
import {OpenFeedback} from '#/plugin/exo/items/open/components/feedback'
import {OpenEditor} from '#/plugin/exo/items/open/components/editor'

// scores
import ScoreManual from '#/plugin/exo/scores/manual'

export default {
  type: 'application/x.open+json',
  name: 'open',
  tags: [trans('question', {}, 'quiz')],
  answerable: true,

  paper: OpenPaper,
  player: OpenPlayer,
  feedback: OpenFeedback,
  components: {
    editor: OpenEditor
  },

  supportScores: () => [
    ScoreManual
  ],

  create: (baseItem) => merge({}, baseItem, OpenItem.defaultProps),

  getCorrectedAnswer: () => new CorrectedAnswer(),
  generateStats: () => ({})
}
