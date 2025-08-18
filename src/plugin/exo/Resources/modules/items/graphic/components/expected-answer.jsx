import React, {useRef, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {asset} from '#/main/app/config/asset'
import {trans} from '#/main/app/intl/translation'
import {Overlay} from '#/main/app/overlays/components/overlay'

import {utils} from '#/plugin/exo/items/graphic/utils'
import {PointableImage} from '#/plugin/exo/items/graphic/components/pointable-image'
import {AnswerTable} from '#/plugin/exo/items/graphic/components/answer-table'
import {SolutionPopover} from '#/plugin/exo/components/solution-popover'

const GraphicExpectedAnswer = props => {
  const popoverContainer = useRef(null)
  const [currentSolution, setCurrentSolution] = useState(null)

  const expectedAreas = props.item.solutions.map((solution, idx) =>
    Object.assign(utils.getAreaPosition(solution.area), {
      number: idx + 1,
      score: solution.score,
      feedback: solution.feedback
    })
  )

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
          pointers={[]}
          areas={expectedAreas}
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

      <AnswerTable
        title={trans('expected_answers', {}, 'quiz')}
        areas={expectedAreas}
        showScore={props.showScore}
        highlightScore={false}
      />
    </div>
  )
}

GraphicExpectedAnswer.propTypes = {
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
  showScore: T.bool.isRequired
}

export {
  GraphicExpectedAnswer
}
