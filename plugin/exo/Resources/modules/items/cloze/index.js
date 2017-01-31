import editor from './editor'
import {ClozePaper} from './paper.jsx'
import {ClozePlayer} from './player.jsx'
import {ClozeFeedback} from './feedback.jsx'

// As a cloze question can have several holes with several choices,
// this function will return an array with the answers that have the biggest score
function expectAnswer(item) {
  let data = []

  if (item.solutions) {
    item.solutions.forEach(s => {
      if (s.answers) {
        let currentAnswer
        let currentScoreMax = 0
        s.answers.forEach(a => {
          if (a.score >= currentScoreMax) {
            currentScoreMax = a.score
            currentAnswer = a
          }
        })
        if (currentAnswer) {
          data.push(currentAnswer)
        }
      }
    })
  }

  return data
}

export default {
  type: 'application/x.cloze+json',
  name: 'cloze',
  paper: ClozePaper,
  player: ClozePlayer,
  feedback: ClozeFeedback,
  editor,
  expectAnswer
}
