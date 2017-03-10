import editor from './editor'
import {OrderingPaper} from './paper.jsx'
import {OrderingPlayer} from './player.jsx'
import {OrderingFeedback} from './feedback.jsx'

function expectAnswer(item) {
  return item.solutions
}

export default {
  type: 'application/x.ordering+json',
  name: 'ordering',
  paper: OrderingPaper,
  player: OrderingPlayer,
  feedback: OrderingFeedback,
  editor,
  expectAnswer
}
