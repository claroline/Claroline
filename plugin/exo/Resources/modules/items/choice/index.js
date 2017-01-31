import editor from './editor'
import {ChoicePaper} from './paper.jsx'
import {ChoicePlayer} from './player.jsx'
import {ChoiceFeedback} from './feedback.jsx'

function expectAnswer(item) {
  return item.solutions
}

export default {
  type: 'application/x.choice+json',
  name: 'choice',
  paper: ChoicePaper,
  player: ChoicePlayer,
  feedback: ChoiceFeedback,
  editor,
  expectAnswer
}
