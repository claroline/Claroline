import editor from './editor'
import {MatchPaper} from './paper.jsx'
import {MatchPlayer} from './player.jsx'
import {MatchFeedback} from './feedback.jsx'
import {CorrectedAnswer, Answerable} from '#/plugin/exo/quiz/correction/components/corrected-answer'
import times from 'lodash/times'

function getCorrectedAnswer(item, answer = {data: []}) {
  const corrected = new CorrectedAnswer()

  item.solutions.forEach(solution => {
    const userAnswer = findAnswer(solution, answer.data)

    if (userAnswer) {
      solution.score > 0 ?
          corrected.addExpected(new Answerable(solution.score)):
          corrected.addUnexpected(new Answerable(solution.score))
    } else {
      if (solution.score > 0)
        corrected.addMissing(new Answerable(solution.score))
    }
  })

  const answersCount = answer && answer.data ? answer.data.length: 0
  times(item.solutions.filter(solution => solution.score > 0).length - answersCount, () => corrected.addPenalty(new Answerable(item.penalty)))

  return corrected
}

function findAnswer(solution, answers) {
  if (!answers) answers = []
  return answers.find(answer => (answer.firstId === solution.firstId) && (answer.secondId === solution.secondId))
}

export default {
  type: 'application/x.match+json',
  name: 'match',
  paper: MatchPaper,
  player: MatchPlayer,
  feedback: MatchFeedback,
  editor,
  getCorrectedAnswer
}
