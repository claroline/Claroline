import editor from './editor'
import {SetPaper} from './paper.jsx'
import {SetPlayer} from './player.jsx'
import {SetFeedback} from './feedback.jsx'
import {CorrectedAnswer, Answerable} from '#/plugin/exo/quiz/correction/components/corrected-answer'
import times from 'lodash/times'

function getCorrectedAnswer(item, answer = {data: []}) {
  const corrected = new CorrectedAnswer()

  item.solutions.associations.forEach(association => {
    const userAnswer = answer && answer.data ? answer.data.find(answer => (answer.itemId === association.itemId) && (answer.setId === association.setId)): null

    userAnswer ?
      corrected.addExpected(new Answerable(association.score)):
      corrected.addMissing(new Answerable(association.score))
  })

  item.solutions.odd.forEach(odd => {
    const penalty = answer && answer.data ? answer.data.find(answer => answer.itemId === odd.itemId): null
    if (penalty) corrected.addPenalty(new Answerable(-odd.score))
  })

  const found = answer && answer.data ? answer.data.length: 0
  times(item.solutions.associations.length - found, () => corrected.addPenalty(new Answerable(item.penalty)))

  return corrected
}

function generateStats(item, papers, withAllParpers) {
  const stats = {
    sets: {},
    unused: {},
    unanswered: 0,
    total: 0
  }
  Object.values(papers).forEach(p => {
    if (withAllParpers || p.finished) {
      let total = 0
      let nbAnswered = 0
      const unusedItems = {}
      // compute the number of times the item is present in the structure of the paper
      p.structure.steps.forEach(s => {
        s.items.forEach(i => {
          if (i.id === item.id) {
            ++total
            ++stats.total
            i.items.forEach(choice => {
              unusedItems[choice.id] = true
            })
          }
        })
      })
      // compute the number of times the item has been answered
      p.answers.forEach(a => {
        if (a.questionId === item.id && a.data) {
          ++nbAnswered
          const unused = Object.assign({}, unusedItems)

          if (a.data) {
            a.data.forEach(d => {
              if (!stats.sets[d.setId]) {
                stats.sets[d.setId] = {}
              }
              stats.sets[d.setId][d.itemId] = stats.sets[d.setId][d.itemId] ? stats.sets[d.setId][d.itemId] + 1 : 1
              unused[d.itemId] = false
            })

            for (let key in unused) {
              if (unused[key]) {
                stats.unused[key] = stats.unused[key] ? stats.unused[key] + 1 : 1
              }
            }
          }
        }
      })
      stats.unanswered += total - nbAnswered
    }
  })

  return stats
}

export default {
  type: 'application/x.set+json',
  name: 'set',
  paper: SetPaper,
  player: SetPlayer,
  feedback: SetFeedback,
  editor,
  getCorrectedAnswer,
  generateStats
}
