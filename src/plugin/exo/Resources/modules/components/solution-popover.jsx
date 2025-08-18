import React, {forwardRef, useId} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {Html} from '#/main/app/components/html'
import {Popover} from '#/main/app/overlays/popover/components/popover'

import {SolutionScore} from '#/plugin/exo/components/score'

const SolutionPopover = forwardRef((props, ref) => {
  const popoverId = useId()

  return (
    <Popover
      id={popoverId}
      placement="bottom"
      {...omit(props, 'solution', 'showScore', 'hasExpectedAnswers')}
      ref={ref}
    >
      <Popover.Body className="selected-answer bg-transparent">
        {props.hasExpectedAnswers && props.showScore && (props.solution.score || 0 === props.solution.score) &&
          <SolutionScore className="ms-0" score={props.solution.score} />
        }

        {props.solution.feedback &&
          <Html className="mt-2">
            {props.solution.feedback}
          </Html>
        }
      </Popover.Body>
    </Popover>
  )
})

SolutionPopover.propTypes = {
  solution: T.shape({
    score: T.number,
    feedback: T.string
  }),
  showScore: T.bool.isRequired,
  hasExpectedAnswers: T.bool.isRequired
}

export {
  SolutionPopover
}
