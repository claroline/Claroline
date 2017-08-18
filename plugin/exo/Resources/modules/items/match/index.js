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

function generateStats(item, papers, withAllParpers) {
  const stats = {
    matches: {},
    unanswered: 0,
    total: 0
  }
  Object.values(papers).forEach(p => {
    if (withAllParpers || p.finished) {
      let total = 0
      let nbAnswered = 0
      // compute the number of times the item is present in the structure of the paper
      p.structure.steps.forEach(s => {
        s.items.forEach(i => {
          if (i.id === item.id) {
            ++total
            ++stats.total
          }
        })
      })
      // compute the number of times the item has been answered
      p.answers.forEach(a => {
        if (a.questionId === item.id && a.data) {
          ++nbAnswered
          a.data.forEach(d => {
            if (!stats.matches[d.firstId]) {
              stats.matches[d.firstId] = {}
            }
            if (!stats.matches[d.firstId][d.secondId]) {
              stats.matches[d.firstId][d.secondId] = 0
            }
            ++stats.matches[d.firstId][d.secondId]
          })
        }
      })
      stats.unanswered += total - nbAnswered
    }
  })

  return stats
}

export default {
  type: 'application/x.match+json',
  name: 'match',
  paper: MatchPaper,
  player: MatchPlayer,
  feedback: MatchFeedback,
  editor,
  getCorrectedAnswer,
  generateStats
}
