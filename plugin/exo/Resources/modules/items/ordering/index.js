import editor from './editor'
import {OrderingPaper} from './paper.jsx'
import {OrderingPlayer} from './player.jsx'
import {OrderingFeedback} from './feedback.jsx'
import {CorrectedAnswer, Answerable} from '#/plugin/exo/quiz/correction/components/corrected-answer'

function getCorrectedAnswer(item, answer = {data: []}) {
  const corrected = new CorrectedAnswer()

  item.solutions.forEach(solution => {
    const userAnswer =
      answer && answer.data ?
        answer.data.find(answer => (answer.itemId === solution.itemId) && (answer.position === solution.position)):
        null

    if (userAnswer) {
      corrected.addExpected(new Answerable(solution.score))
    } else {
      corrected.addMissing(new Answerable(solution.score))
      corrected.addPenalty(new Answerable(item.penalty))
    }
  })

  return corrected
}

function generateStats(item, papers, withAllParpers) {
  const stats = {
    orders: {},
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
          let orderingKey = ''
          a.data.forEach(d => {
            orderingKey += d.itemId
            unused[d.itemId] = false
          })

          if (!stats.orders[orderingKey]) {
            stats.orders[orderingKey] = {
              data: a.data,
              count: 0
            }
          }
          ++stats.orders[orderingKey].count

          for (let key in unused) {
            if (unused[key]) {
              stats.unused[key] = stats.unused[key] ? stats.unused[key] + 1 : 1
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
  type: 'application/x.ordering+json',
  name: 'ordering',
  paper: OrderingPaper,
  player: OrderingPlayer,
  feedback: OrderingFeedback,
  editor,
  getCorrectedAnswer,
  generateStats
}
