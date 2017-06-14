import editor from './editor'
import {GraphicPaper} from './paper.jsx'
import {GraphicPlayer} from './player.jsx'
import {GraphicFeedback} from './feedback.jsx'
import {CorrectedAnswer, Answerable} from '#/plugin/exo/quiz/correction/components/corrected-answer'

function getCorrectedAnswer(item, answers) {
  const corrected = new CorrectedAnswer()

  item.solutions.forEach(solution => {
    if (answers && answers.data) {
      answers.data.forEach(coords => {
        if (isPointInArea(solution.area, coords.x, coords.y)) {
          solution.score > 0 ?
            corrected.addExpected(new Answerable(solution.score)):
            corrected.addUnexpected(new Answerable(solution.score))
        } else if (solution.score > 0) {
          corrected.addMissing(new Answerable(solution.score))
        }
      })
    }
  })

  return corrected
}

function isPointInArea(area, x, y) {

  if (area.shape !== 'circle') {
    const res =
      x >= area.coords[0].x &&
      x <= area.coords[1].x &&
      y >= area.coords[0].y &&
      y <= area.coords[1].y

    return res
  } else {
    const size = area.radius * 2
    // must be circle
    const r = size / 2

    // coordinates relative to the circle center
    x = Math.abs(area.center.x - x)
    y = Math.abs(area.center.y - y)

    // inside the circle if distance to center <= radius
    return x * x + y * y <= r * r
  }
}

export default {
  type: 'application/x.graphic+json',
  name: 'graphic',
  paper: GraphicPaper,
  player: GraphicPlayer,
  feedback: GraphicFeedback,
  editor,
  getCorrectedAnswer
}
