import editor from './editor'
import {ChoicePaper} from './paper.jsx'
import {ChoicePlayer} from './player.jsx'
import {ChoiceFeedback} from './feedback.jsx'
import {CorrectedAnswer, Answerable} from '#/plugin/exo/quiz/correction/components/corrected-answer'

function getCorrectedAnswer(item, answers = null) {
  const corrected = new CorrectedAnswer()

  item.solutions.forEach(choice => {
    const score = choice.score
    if (answers && answers.data && answers.data.indexOf(choice.id) > -1) {
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
          ++nbAnswered
          a.data.forEach(d => {
            if (!stats.choices[d]) {
              stats.choices[d] = 0
            }
            ++stats.choices[d]
          })
        }
      })
      stats.unanswered += total - nbAnswered
    }
  })

  return stats
}

export default {
  type: 'application/x.choice+json',
  name: 'choice',
  paper: ChoicePaper,
  player: ChoicePlayer,
  feedback: ChoiceFeedback,
  editor,
  getCorrectedAnswer,
  generateStats
}
