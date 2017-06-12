import editor from './editor'
import {WordsPaper} from './paper.jsx'
import {WordsPlayer} from './player.jsx'
import {WordsFeedback} from './feedback.jsx'
import {CorrectedAnswer, Answerable} from '#/plugin/exo/quiz/correction/components/corrected-answer'

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

export default {
  type: 'application/x.words+json',
  name: 'words',
  paper: WordsPaper,
  player: WordsPlayer,
  feedback: WordsFeedback,
  editor,
  getCorrectedAnswer
}
