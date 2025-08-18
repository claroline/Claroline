import React, {useRef, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {asset} from '#/main/app/config/asset'
import {Overlay} from '#/main/app/overlays/components/overlay'

import {SolutionPopover} from '#/plugin/exo/components/solution-popover'
import {POINTER_PLACED, POINTER_CORRECT, POINTER_WRONG} from '#/plugin/exo/items/graphic/constants'
import {PointableImage} from '#/plugin/exo/items/graphic/components/pointable-image'
import {utils} from '#/plugin/exo/items/graphic/utils'
import {AnswerTable} from '#/plugin/exo/items/graphic/components/answer-table'


const GraphicFeedback = props => {
  const popoverContainer = useRef(null)
  const [currentSolution, setCurrentSolution] = useState(null)

  const pointedAreas = props.item.solutions
    .filter(solution =>
      !!props.answer.find(coords => utils.findArea(coords, [solution]))
    )
    .map((solution, idx) => Object.assign(utils.getAreaPosition(solution.area), {
      number: idx + 1,
      score: solution.score,
      feedback: solution.feedback
    }))

  return (
    <div className="graphic-paper">
      <p className="text-body-secondary fs-sm">
        <span className="fa fa-circle-info me-2" aria-hidden={true} />
        {trans('graphic_answer_help', {}, 'quiz')}
      </p>

      <div className="img-zone position-relative" ref={popoverContainer}>
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
              feedback: area && area.feedback,
              score: area && area.score ? area.score : 0
            }
          })}
          hasExpectedAnswers={props.item.hasExpectedAnswers}
          onPointerClick={(pointer, e) => {
            setCurrentSolution({
              solution: pointer,
              target: e.target
            })
          }}
        />

        <Overlay
          show={!!currentSolution}
          target={get(currentSolution, 'target')}
          placement="bottom"
          container={popoverContainer}
          rootClose={true}
          rootCloseEvent="click"
          onHide={() => setCurrentSolution(null)}
        >
          <SolutionPopover
            solution={get(currentSolution, 'solution', {})}
            showScore={props.showScore}
            hasExpectedAnswers={props.item.hasExpectedAnswers}
          />
        </Overlay>
      </div>

      {props.item.hasExpectedAnswers && pointedAreas.length > 0 &&
        <AnswerTable title={trans('your_answers', {}, 'quiz')} areas={pointedAreas} showScore={props.showScore} highlightScore={true}/>
      }
    </div>
  )
}

GraphicFeedback.propTypes = {
  item: T.shape({
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
  })).isRequired,
  showScore: T.bool
}

GraphicFeedback.defaultProps = {
  answer: []
}

export {
  GraphicFeedback
}
