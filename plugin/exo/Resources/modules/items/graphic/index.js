import editor from './editor'
import {GraphicPaper} from './paper.jsx'
import {GraphicPlayer} from './player.jsx'
import {GraphicFeedback} from './feedback.jsx'

function expectAnswer(item) {
  return item.solutions.filter(solution => solution.score > 0)
}

export default {
  type: 'application/x.graphic+json',
  name: 'graphic',
  paper: GraphicPaper,
  player: GraphicPlayer,
  feedback: GraphicFeedback,
  editor,
  expectAnswer
}
