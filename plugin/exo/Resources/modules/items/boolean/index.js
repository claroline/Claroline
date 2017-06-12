import editor from './editor'
import {BooleanPaper} from './paper.jsx'
import {BooleanPlayer} from './player.jsx'
import {BooleanFeedback} from './feedback.jsx'
import {CorrectedAnswer, Answerable} from '#/plugin/exo/quiz/correction/components/corrected-answer'

function getCorrectedAnswer(item, answer = null) {
  const corrected = new CorrectedAnswer()

  item.choices.forEach(choice => {
    const score = choice._score
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

export default {
  type: 'application/x.boolean+json',
  name: 'boolean',
  paper: BooleanPaper,
  player: BooleanPlayer,
  feedback: BooleanFeedback,
  editor,
  getCorrectedAnswer
}
