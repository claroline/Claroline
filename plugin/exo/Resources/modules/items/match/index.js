import editor from './editor'
import {MatchPaper} from './paper.jsx'
import {MatchPlayer} from './player.jsx'
import {MatchFeedback} from './feedback.jsx'

function expectAnswer(item) {
  return item.solutions
}

export default {
  type: 'application/x.match+json',
  name: 'match',
  paper: MatchPaper,
  player: MatchPlayer,
  feedback: MatchFeedback,
  editor,
  expectAnswer
}
