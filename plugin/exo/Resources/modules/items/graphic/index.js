import {trans} from '#/main/app/intl/translation'

import editor from '#/plugin/exo/items/graphic/editor'
import {GraphicPaper} from '#/plugin/exo/items/graphic/paper.jsx'
import {GraphicEditor} from '#/plugin/exo/items/graphic/components/editor.jsx'
import {GraphicPlayer} from '#/plugin/exo/items/graphic/player.jsx'
import {GraphicFeedback} from '#/plugin/exo/items/graphic/feedback.jsx'
import {CorrectedAnswer, Answerable} from '#/plugin/exo/quiz/correction/components/corrected-answer'
import {GraphicItem as GraphicItemTypes} from '#/plugin/exo/items/graphic/prop-types'

function getCorrectedAnswer(item, answers) {
  const corrected = new CorrectedAnswer()

  item.solutions.forEach(solution => {
    if (answers && answers.data) {
      answers.data.forEach(coords => {
        if (isPointInArea(solution.area, coords.x, coords.y)) {
          solution.score > 0 ?
            corrected.addExpected(new Answerable(solution.score, solution.area.id)):
            corrected.addUnexpected(new Answerable(solution.score, solution.area.id))
        } else if (solution.score > 0) {
          corrected.addMissing(new Answerable(solution.score, solution.area.id))
        }
      })
    } else {
      corrected.addMissing(new Answerable(solution.score, solution.area.id))
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

function generateStats(item, papers, withAllParpers) {
  const stats = {
    areas: {},
    unanswered: 0,
    total: 0
  }
  Object.values(papers).forEach(p => {
    if (withAllParpers || p.finished) {
      let total = 0
      let nbAnswered = 0
      // compute the number of times the item is present in the structure of the paper and initialize acceptable pairs
      p.structure.steps.forEach(structure => {
        structure.items.forEach(i => {
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
          const areasToInc = {}
          a.data.forEach(d => {
            let isInArea = false
            item.solutions.forEach(s => {
              if (isPointInArea(s.area, d.x, d.y)) {
                areasToInc[s.area.id] = true
                isInArea = true
              }
            })

            if (!isInArea) {
              stats.areas['_others'] = stats.areas['_others'] ? stats.areas['_others'] + 1 : 1
            }
          })
          for (let areaId in areasToInc) {
            stats.areas[areaId] = stats.areas[areaId] ? stats.areas[areaId] + 1 : 1
          }
        }
      })
      stats.unanswered += total - nbAnswered
    }
  })

  return stats
}

export default {
  type: 'application/x.graphic+json',
  name: 'graphic',
  tags: [trans('question', {}, 'quiz')],
  answerable: true,

  paper: GraphicPaper,
  player: GraphicPlayer,
  feedback: GraphicFeedback,
  editor,
  getCorrectedAnswer,
  generateStats,

  components: {
    editor: GraphicEditor
  },

  create: (item) => {
  //return item
    return Object.assign(item, GraphicItemTypes.defaultProps)
  }
}
