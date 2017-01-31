import editor from './editor'
import {WordsPaper} from './paper.jsx'
import {WordsPlayer} from './player.jsx'
import {WordsFeedback} from './feedback.jsx'

function expectAnswer(item) {
  return item.solutions
}

export default {
  type: 'application/x.words+json',
  name: 'words',
  paper: WordsPaper,
  player: WordsPlayer,
  feedback: WordsFeedback,
  editor,
  expectAnswer
}
