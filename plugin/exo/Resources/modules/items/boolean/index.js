import editor from './editor'
import {BooleanPaper} from './paper.jsx'
import {BooleanPlayer} from './player.jsx'
import {BooleanFeedback} from './feedback.jsx'

function expectAnswer(item) {
  return item.solutions
}

export default {
  type: 'application/x.boolean+json',
  name: 'boolean',
  paper: BooleanPaper,
  player: BooleanPlayer,
  feedback: BooleanFeedback,
  editor,
  expectAnswer
}
