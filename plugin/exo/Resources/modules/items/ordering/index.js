import editor from './editor'
import {OrderingPaper} from './paper.jsx'
import {OrderingPlayer} from './player.jsx'
import {OrderingFeedback} from './feedback.jsx'
import {CorrectedAnswer, Answerable} from '#/plugin/exo/quiz/correction/components/corrected-answer'

function getCorrectedAnswer(item, answer = {data: []}) {
  const corrected = new CorrectedAnswer()

  item.solutions.forEach(solution => {
    const userAnswer = answer.data.find(answer => (answer.itemId === solution.itemId) && (answer.position === solution.position))

    if (userAnswer) {
      corrected.addExpected(new Answerable(solution.score))
    } else {
      corrected.addMissing(new Answerable(solution.score))
      corrected.addPenalty(new Answerable(item.penalty))
    }
  })

  return corrected
}

export default {
  type: 'application/x.ordering+json',
  name: 'ordering',
  paper: OrderingPaper,
  player: OrderingPlayer,
  feedback: OrderingFeedback,
  editor,
  getCorrectedAnswer
}
