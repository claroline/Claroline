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
    const userAnswer = answer && answer.data ? findUserAnswer(solution, answer): null

    if (userAnswer) {
      solution.score > 0 ?
        corrected.addExpected(new Answerable(solution.score)):
        corrected.addUnexpected(new Answerable(solution.score))
    } else {
      if (solution.score > 0) corrected.addMissing(new Answerable(solution.score))
    }
  })

  item.solutions.filter(solution => solution.itemIds.length === 1).forEach(oddity => {
    if (answer && answer.data) {
      const found = answer.data.find(answer => answer.indexOf(oddity.itemIds[0]) >= 0)
      if (found) {
        corrected.addPenalty(new Answerable(-oddity.score))
      }
    }
  })

  if (answer && answer.data) {
    times(item.solutions.filter(solution => solution.score > 0).length - answer.data.length, () => corrected.addPenalty(new Answerable(item.penalty)))
  }

  return corrected
}

function findUserAnswer(solution, answer) {
  return answer.data.find(answer =>  JSON.stringify(cloneDeep(solution.itemIds).sort()) === JSON.stringify(cloneDeep(answer).sort()))
}

function generateStats(item, papers, withAllParpers) {
  const stats = {
    paired: {},
    unpaired: {},
    unanswered: 0,
    total: 0
  }
  const valid = {}
  Object.values(papers).forEach(p => {
    if (withAllParpers || p.finished) {
      let total = 0
      let nbAnswered = 0
      const unusedItems = {}
      // compute the number of times the item is present in the structure of the paper and initialize acceptable pairs
      p.structure.steps.forEach(structure => {
        structure.items.forEach(i => {
          if (i.id === item.id) {
            ++total
            ++stats.total
            i.items.forEach(choice => {
              unusedItems[choice.id] = true
            })
            // build acceptable pairs from solutions
            i.solutions.forEach(solution => {
              if (solution.itemIds && solution.itemIds.length > 1) {
                if (!valid[solution.itemIds[0]]) {
                  valid[solution.itemIds[0]] = {}
                }
                valid[solution.itemIds[0]][solution.itemIds[1]] = true

                if (solution.ordered) {
                  if (!valid[solution.itemIds[1]]) {
                    valid[solution.itemIds[1]] = {}
                  }
                  valid[solution.itemIds[1]][solution.itemIds[0]] = true
                }
              }
            })
            // build remaining acceptable pairs to group inversed pairs together
            i.items.forEach(i1 => {
              i.items.forEach(i2 => {
                if ((!valid[i1.id] || !valid[i1.id][i2.id]) && (!valid[i2.id] || !valid[i2.id][i1.id])) {
                  if (!valid[i1.id]) {
                    valid[i1.id] = {}
                  }
                  valid[i1.id][i2.id] = true
                }
              })
            })
          }
        })
      })
      // compute the number of times the item has been answered
      p.answers.forEach(a => {
        if (a.questionId === item.id && (a.data || a.score > 0)) {
          ++nbAnswered
          const unused = Object.assign({}, unusedItems)

          if (a.data) {
            a.data.forEach(d => {
              const first = valid[d[0]] && valid[d[0]][d[1]] ? d[0] : d[1]
              const second = valid[d[0]] && valid[d[0]][d[1]] ? d[1] : d[0]

              if (!stats.paired[first]) {
                stats.paired[first] = {}
              }
              if (!stats.paired[first][second]) {
                stats.paired[first][second] = 0
              }
              ++stats.paired[first][second]
              unused[first] = false
              unused[second] = false
            })

            for (let key in unused) {
              if (unused[key]) {
                stats.unpaired[key] = stats.unpaired[key] ? stats.unpaired[key] + 1 : 1
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
  type: 'application/x.pair+json',
  name: 'pair',
  paper: PairPaper,
  player: PairPlayer,
  feedback: PairFeedback,
  editor,
  getCorrectedAnswer,
  generateStats
}
