import editor from './editor'
import {SetPaper} from './paper.jsx'
import {SetPlayer} from './player.jsx'
import {SetFeedback} from './feedback.jsx'
import {CorrectedAnswer, Answerable} from '#/plugin/exo/quiz/correction/components/corrected-answer'
import times from 'lodash/times'

function getCorrectedAnswer(item, answer = {data: []}) {
  const corrected = new CorrectedAnswer()

  item.solutions.associations.forEach(association => {
    const userAnswer = answer ? answer.data.find(answer => (answer.itemId === association.itemId) && (answer.setId === association.setId)): null

    userAnswer ?
      corrected.addExpected(new Answerable(association.score)):
      corrected.addMissing(new Answerable(association.score))
  })

  item.solutions.odd.forEach(odd => {
    const penalty = answer ? answer.data.find(answer => answer.itemId === odd.itemId): null
    if (penalty) corrected.addPenalty(new Answerable(-odd.score))
  })

  const found = answer ? answer.data.length: 0
  times(item.solutions.associations.length - found, () => corrected.addPenalty(new Answerable(item.penalty)))

  return corrected
}

export default {
  type: 'application/x.set+json',
  name: 'set',
  paper: SetPaper,
  player: SetPlayer,
  feedback: SetFeedback,
  editor,
  getCorrectedAnswer
}
