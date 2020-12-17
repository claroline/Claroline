import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/app/config/asset'
import {trans} from '#/main/app/intl/translation'

import {POINTER_PLACED, POINTER_CORRECT, POINTER_WRONG, SHAPE_RECT} from '#/plugin/exo/items/graphic/constants'
import {utils} from '#/plugin/exo/items/graphic/utils'
import {PaperTabs} from '#/plugin/exo/items/components/paper-tabs'
import {PointableImage} from '#/plugin/exo/items/graphic/components/pointable-image'
import {AnswerTable} from '#/plugin/exo/items/graphic/components/answer-table'
import {AnswerStatsTable} from '#/plugin/exo/items/graphic/components/answer-stats-table'

export const GraphicPaper = props => {
  const pointedAreas = props.item.solutions
    .filter(solution =>
      !!props.answer.find(coords => utils.findArea(coords, [solution]))
    )
    .map((solution, idx) => Object.assign(getAreaPosition(solution.area), {
      number: idx + 1,
      score: solution.score,
      feedback: solution.feedback
    }))
  const expectedAreas = props.item.solutions.map((solution, idx) =>
    Object.assign(getAreaPosition(solution.area), {
      number: idx + 1,
      score: solution.score,
      feedback: solution.feedback
    })
  )

  return (
    <PaperTabs
      id={props.item.id}
      showExpected={props.showExpected}
      showStats={props.showStats}
      showYours={props.showYours}
      yours={
        <div className="graphic-paper">
          <div className="img-zone" style={{position: 'relative'}}>
            <PointableImage
              src={props.item.image.data || asset(props.item.image.url)}
              absWidth={props.item.image.width}
              pointers={props.answer.map(coords => {
                const area = utils.findArea(coords, props.item.solutions)
                return {
                  absX: coords.x,
                  absY: coords.y,
                  type: props.item.hasExpectedAnswers ?
                    (area && (area.score > 0)) ? POINTER_CORRECT : POINTER_WRONG :
                    POINTER_PLACED,
                  feedback: undefined
                }
              })}
              areas={pointedAreas}
              hasExpectedAnswers={props.item.hasExpectedAnswers}
            />
          </div>
          {props.item.hasExpectedAnswers && pointedAreas.length > 0 &&
            <AnswerTable title={trans('your_answers', {}, 'quiz')} areas={pointedAreas} showScore={props.showScore} highlightScore={true}/>
          }
        </div>
      }
      expected={
        <div className="graphic-paper">
          <div className="img-zone" style={{position: 'relative'}}>
            <PointableImage
              src={props.item.image.data || asset(props.item.image.url)}
              absWidth={props.item.image.width}
              pointers={[]}
              areas={expectedAreas}
            />
          </div>
          <AnswerTable title={trans('expected_answers', {}, 'quiz')} areas={expectedAreas} showScore={props.showScore} highlightScore={false}/>
        </div>
      }
      stats={
        <div className="graphic-paper">
          <div className="img-zone" style={{position: 'relative'}}>
            <PointableImage
              src={props.item.image.data || asset(props.item.image.url)}
              absWidth={props.item.image.width}
              pointers={[]}
              areas={expectedAreas}
            />
          </div>
          <AnswerStatsTable
            title={trans('stats', {}, 'quiz')}
            areas={expectedAreas}
            stats={props.stats}
            hasExpectedAnswers={props.item.hasExpectedAnswers}
          />
        </div>
      }
    />
  )
}

GraphicPaper.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    image: T.oneOfType([
      T.shape({
        data: T.string.isRequired,
        width: T.number.isRequired
      }),
      T.shape({
        url: T.string.isRequired,
        width: T.number.isRequired
      })
    ]).isRequired,
    solutions: T.arrayOf(T.shape({
      area: T.shape({
        id: T.string.isRequired,
        shape: T.string.isRequired,
        color: T.string.isRequired
      }).isRequired
    })).isRequired,
    hasExpectedAnswers: T.bool.isRequired
  }).isRequired,
  answer: T.arrayOf(T.shape({
    x: T.number.isRequired,
    y: T.number.isRequired
  })),
  showScore: T.bool.isRequired,
  showExpected: T.bool.isRequired,
  showYours: T.bool.isRequired,
  showStats: T.bool.isRequired,
  stats: T.shape({
    areas: T.object,
    unanswered: T.number,
    total: T.number
  })
}

GraphicPaper.defaultProps = {
  answer: []
}

function getAreaPosition(area) {
  if (area.shape === SHAPE_RECT) {
    return Object.assign({}, area, {
      top: area.coords[0].y,
      left: area.coords[0].x,
      width: area.coords[1].x - area.coords[0].x,
      height: area.coords[1].y - area.coords[0].y,
      borderRadius: 0
    })
  }

  return Object.assign({}, area, {
    top: area.center.y - area.radius,
    left: area.center.x - area.radius,
    width: area.radius * 2,
    height: area.radius * 2,
    borderRadius: area.radius
  })
}
