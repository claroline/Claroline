import {trans} from '#/main/app/intl/translation'

import editor from './editor'
import {WordsPaper} from './paper.jsx'
import {WordsPlayer} from './player.jsx'
import {WordsFeedback} from './feedback.jsx'
import {CorrectedAnswer, Answerable} from '#/plugin/exo/quiz/correction/components/corrected-answer'
import {utils} from './utils/utils'

function getCorrectedAnswer(item, answer = {data: ''}) {
  const corrected = new CorrectedAnswer()

  item.solutions.forEach(solution => {
    const hasKeyword = containsKeyword(solution.text, solution.caseSensitive, answer ? answer.data: '')

    if (hasKeyword) {
      solution.score > 0 ?
        corrected.addExpected(new Answerable(solution.score)):
        corrected.addUnexpected(new Answerable(solution.score))
    } else {
      if (solution.score > 0) corrected.addMissing(new Answerable(solution.score))
    }
  })

  return corrected
}

function escapeRegExp(str) {
  return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, '\\$&')
}

function containsKeyword(keyword, caseSensitive, text = '') {
  const regex = new RegExp(escapeRegExp(keyword), caseSensitive ? '': 'i')
  return regex.test(text)
}

function generateStats(item, papers, withAllParpers) {
  const stats = {
    words: {},
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
          const key = utils.getKey(a.data, item.solutions)
          stats.words[key] = stats.words[key] ? stats.words[key] + 1 : 1
        }
      })
      stats.unanswered += total - nbAnswered
    }
  })

  return stats
}

export default {
  type: 'application/x.words+json',
  name: 'words',
  tags: [trans('question', {}, 'quiz')],
  answerable: true,

  paper: WordsPaper,
  player: WordsPlayer,
  feedback: WordsFeedback,
  editor,
  getCorrectedAnswer,
  generateStats
}
