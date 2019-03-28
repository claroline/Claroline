import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'

import {OrderingItem as OrderingItemType} from '#/plugin/exo/items/ordering/prop-types'
import {OrderingEditor} from '#/plugin/exo/items/ordering/components/editor'
// old
import {OrderingPaper} from '#/plugin/exo/items/ordering/paper.jsx'
import {OrderingPlayer} from '#/plugin/exo/items/ordering/player.jsx'
import {OrderingFeedback} from '#/plugin/exo/items/ordering/feedback.jsx'
import {CorrectedAnswer, Answerable} from '#/plugin/exo/quiz/correction/components/corrected-answer'

// scores
import ScoreFixed from '#/plugin/exo/scores/fixed'
import ScoreSum from '#/plugin/exo/scores/sum'

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
  tags: [trans('question', {}, 'quiz')],
  answerable: true,

  components: {
    editor: OrderingEditor
  },

  supportScores: () => [
    ScoreFixed,
    ScoreSum
  ],

  create: (orderingItem) => merge({}, OrderingItemType.defaultProps, orderingItem),

  // old
  paper: OrderingPaper,
  player: OrderingPlayer,
  feedback: OrderingFeedback,
  getCorrectedAnswer,
  generateStats
}
