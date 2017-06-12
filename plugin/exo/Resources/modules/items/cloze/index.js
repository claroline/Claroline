import editor from './editor'
import {ClozePaper} from './paper.jsx'
import {ClozePlayer} from './player.jsx'
import {ClozeFeedback} from './feedback.jsx'
import {CorrectedAnswer, Answerable} from '#/plugin/exo/quiz/correction/components/corrected-answer'

function getCorrectedAnswer(item, answers = null) {
  const corrected = new CorrectedAnswer()

  item.solutions.forEach(solution => {
    const hole = item.holes.find(hole => hole.id === solution.holeId)
    const answer = answer ? answers.data.find(answer => answer.holeId === hole.id): null
    const expected = findSolutionExpectedAnswer(solution)

    if (answer) {
      if (answer.answerText === expected.text) {
        corrected.addExpected(new Answerable(expected.score))
      } else {
        const userAnswer = solution.answers.find(solutionAnswer => solutionAnswer.text === answer.answerText)
        corrected.addUnexpected(new Answerable(userAnswer ? userAnswer.score: 0))
      }
    } else {
      corrected.addMissing(new Answerable(expected.score))
    }
  })

  return corrected
}

function findSolutionExpectedAnswer(solution) {
  let expected = null

  solution.answers.forEach(answer => {
    if (!expected || expected.score < answer.score) {
      expected = answer
    }
  })

  return expected
}

export default {
  type: 'application/x.cloze+json',
  name: 'cloze',
  paper: ClozePaper,
  player: ClozePlayer,
  feedback: ClozeFeedback,
  editor,
  getCorrectedAnswer
}
