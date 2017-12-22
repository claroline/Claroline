import editor from './editor'
import {BooleanPaper} from './paper.jsx'
import {BooleanPlayer} from './player.jsx'
import {BooleanFeedback} from './feedback.jsx'
import {CorrectedAnswer, Answerable} from '#/plugin/exo/quiz/correction/components/corrected-answer'

function getCorrectedAnswer(item, answer = null) {
  const corrected = new CorrectedAnswer()

  item.solutions.forEach(choice => {
    const score = choice.score
    if (answer && answer.data === choice.id) {
      score > 0 ?
        corrected.addExpected(new Answerable(score)) :
        corrected.addUnexpected(new Answerable(score))
    } else {
      if (score > 0) corrected.addMissing(new Answerable(score))
    }
  })

  return corrected
}

function generateStats(item, papers, withAllParpers) {
  const stats = {
    choices: {},
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
          if (!stats.choices[a.data]) {
            stats.choices[a.data] = 0
          }
          ++stats.choices[a.data]
          ++nbAnswered
        }
      })
      stats.unanswered += total - nbAnswered
    }
  })

  return stats
}

export default {
  type: 'application/x.boolean+json',
  name: 'boolean',
  paper: BooleanPaper,
  player: BooleanPlayer,
  feedback: BooleanFeedback,
  editor,
  getCorrectedAnswer,
  generateStats
}
