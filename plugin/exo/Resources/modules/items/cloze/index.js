import editor from './editor'
import {ClozePaper} from './paper.jsx'
import {ClozePlayer} from './player.jsx'
import {ClozeFeedback} from './feedback.jsx'
import {CorrectedAnswer, Answerable} from '#/plugin/exo/quiz/correction/components/corrected-answer'
import {utils} from './utils/utils'

function getCorrectedAnswer(item, answers = null) {
  const corrected = new CorrectedAnswer()

  item.solutions.forEach(solution => {
    const hole = item.holes.find(hole => hole.id === solution.holeId)
    const answer = answers ? answers.data.find(answer => answer.holeId === hole.id): null
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

function generateStats(item, papers, withAllParpers) {
  const stats = {
    holes: {},
    unanswered: 0,
    total: 0
  }
  Object.values(papers).forEach(p => {
    if (withAllParpers || p.finished) {
      let total = 0
      let nbAnswered = 0
      const answered = {}
      // compute the number of times the item is present in the structure of the paper
      p.structure.steps.forEach(s => {
        s.items.forEach(i => {
          if (i.id === item.id) {
            ++total
            ++stats.total

            if (i.solutions) {
              i.solutions.forEach(s => {
                answered[s.holeId] = answered[s.holeId] ? answered[s.holeId] + 1 : 1
              })
            }
          }
        })
      })
      // compute the number of times the item has been answered
      p.answers.forEach(a => {
        if (a.questionId === item.id && a.data) {
          ++nbAnswered
          a.data.forEach(d => {
            const key = utils.getKey(d.holeId, d.answerText, item.solutions)

            if (!stats.holes[d.holeId]) {
              stats.holes[d.holeId] = {}
            }
            stats.holes[d.holeId][key] = stats.holes[d.holeId][key] ? stats.holes[d.holeId][key] + 1 : 1

            if (answered[d.holeId]) {
              --answered[d.holeId]
            }
          })
        }
      })
      const nbUnanswered = total - nbAnswered

      for (let holeId in answered) {
        if (answered[holeId] - nbUnanswered > 0) {
          if (!stats.holes[holeId]) {
            stats.holes[holeId] = {}
          }
          stats.holes[holeId]['_unanswered'] = stats.holes[holeId]['_unanswered'] ?
            stats.holes[holeId]['_unanswered'] + (answered[holeId] - nbUnanswered) :
            answered[holeId] - nbUnanswered
        }
      }
      stats.unanswered += nbUnanswered
    }
  })

  return stats
}

export default {
  type: 'application/x.cloze+json',
  name: 'cloze',
  paper: ClozePaper,
  player: ClozePlayer,
  feedback: ClozeFeedback,
  editor,
  getCorrectedAnswer,
  generateStats
}
