import editor from './editor'
import {OpenPaper} from './paper.jsx'
import {OpenPlayer} from './player.jsx'
import {OpenFeedback} from './feedback.jsx'

export default {
  type: 'application/x.open+json',
  name: 'open',
  paper: OpenPaper,
  player: OpenPlayer,
  feedback: OpenFeedback,
  editor
}
