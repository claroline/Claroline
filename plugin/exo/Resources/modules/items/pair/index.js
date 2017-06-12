import editor from './editor'
import {PairPaper} from './paper.jsx'
import {PairPlayer} from './player.jsx'
import {PairFeedback} from './feedback.jsx'
import {CorrectedAnswer, Answerable} from '#/plugin/exo/quiz/correction/components/corrected-answer'
import cloneDeep from 'lodash/cloneDeep'
import times from 'lodash/times'

function getCorrectedAnswer(item, answer = {data: []}) {
  const corrected = new CorrectedAnswer()

  //look for good answers
  item.solutions.forEach(solution => {
    const userAnswer = findUserAnswer(solution, answer)

    if (userAnswer) {
      solution.score > 0 ?
        corrected.addExpected(new Answerable(solution.score)):
        corrected.addUnexpected(new Answerable(solution.score))
    } else {
      if (solution.score > 0) corrected.addMissing(new Answerable(solution.score))
    }
  })

  item.solutions.filter(solution => solution.itemIds.length === 1).forEach(oddity => {
    const found = answer.data.find(answer => answer.indexOf(oddity.itemIds[0]) >= 0)
    if (found) {
      corrected.addPenalty(new Answerable(-oddity.score))
    }
  })

  times(item.solutions.filter(solution => solution.score > 0).length - answer.data.length, () => corrected.addPenalty(new Answerable(item.penalty)))

  return corrected
}

function findUserAnswer(solution, answer) {
  return answer.data.find(answer =>  JSON.stringify(cloneDeep(solution.itemIds).sort()) === JSON.stringify(cloneDeep(answer).sort()))
}
export default {
  type: 'application/x.pair+json',
  name: 'pair',
  paper: PairPaper,
  player: PairPlayer,
  feedback: PairFeedback,
  editor,
  getCorrectedAnswer
}
