import {trans} from '#/main/app/intl/translation'

import {CorrectedAnswer, Answerable} from '#/plugin/exo/quiz/correction/components/corrected-answer'
import {GraphicItem as GraphicItemTypes} from '#/plugin/exo/items/graphic/prop-types'

// components
import {GraphicPaper} from '#/plugin/exo/items/graphic/components/paper'
import {GraphicEditor} from '#/plugin/exo/items/graphic/components/editor'
import {GraphicPlayer} from '#/plugin/exo/items/graphic/components/player'
import {GraphicFeedback} from '#/plugin/exo/items/graphic/components/feedback'

// scores
import ScoreSum from '#/plugin/exo/scores/sum'

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
    return x >= area.coords[0].x &&
      x <= area.coords[1].x &&
      y >= area.coords[0].y &&
      y <= area.coords[1].y
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
  getCorrectedAnswer,
  generateStats,

  components: {
    editor: GraphicEditor
  },

  supportScores: () => [
    ScoreSum
  ],

  create: (item) => Object.assign(item, GraphicItemTypes.defaultProps)
}
