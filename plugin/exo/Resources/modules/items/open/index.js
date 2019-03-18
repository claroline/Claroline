import {trans} from '#/main/app/intl/translation'

import editor from '#/plugin/exo/items/open/editor'
import {OpenPaper} from '#/plugin/exo/items/open/paper.jsx'
import {OpenPlayer} from '#/plugin/exo/items/open/player.jsx'
import {OpenFeedback} from '#/plugin/exo/items/open/feedback.jsx'
import {OpenEditor} from '#/plugin/exo/items/open/components/editor'
import {OpenItem as OpenItemTypes} from '#/plugin/exo/items/open/prop-types'

import {CorrectedAnswer} from '#/plugin/exo/quiz/correction/components/corrected-answer'

function getCorrectedAnswer() {
  return new CorrectedAnswer()
}

function generateStats() {
  return {}
}

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

  create: item => {
    item.score = OpenItemTypes.defaultProps.score

    return item
  },

  editor,
  getCorrectedAnswer,
  generateStats
}
