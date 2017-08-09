import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/core/asset'
import {POINTER_CORRECT, POINTER_WRONG, SHAPE_RECT} from './enums'
import {findArea} from './player'
import {PaperTabs} from '../components/paper-tabs.jsx'
import {PointableImage} from './components/pointable-image.jsx'
import {AnswerTable} from './components/answer-table.jsx'
import {tex} from '#/main/core/translation'

export const GraphicPaper = props => {
  const pointedAreas = props.item.solutions
    .filter(solution =>
      !!props.answer.find(coords => findArea(coords, [solution]))
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
      hideExpected={props.hideExpected}
      yours={
        <div className="graphic-paper">
          <div className="img-zone" style={{position: 'relative'}}>
            <PointableImage
              src={props.item.image.data || asset(props.item.image.url)}
              absWidth={props.item.image.width}
              pointers={props.answer.map(coords => {
                const area = findArea(coords, props.item.solutions)
                return {
                  absX: coords.x,
                  absY: coords.y,
                  type: (area && (area.score > 0)) ? POINTER_CORRECT : POINTER_WRONG,
                  feedback: undefined
                }
              })}
              areas={pointedAreas}
            />
          </div>
          {pointedAreas.length > 0 &&
            <AnswerTable title={tex('your_answers')} areas={pointedAreas} showScore={props.showScore} highlightScore={true}/>
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
          <AnswerTable title={tex('expected_answers')} areas={expectedAreas} showScore={props.showScore} highlightScore={false}/>
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
    })).isRequired
  }).isRequired,
  answer: T.arrayOf(T.shape({
    x: T.number.isRequired,
    y: T.number.isRequired
  })),
  showScore: T.bool.isRequired,
  hideExpected: T.bool.isRequired
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
