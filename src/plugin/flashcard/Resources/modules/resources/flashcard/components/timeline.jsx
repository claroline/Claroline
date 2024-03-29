import React from 'react'
import classes from 'classnames'
import {PropTypes as T} from 'prop-types'

import {TooltipOverlay} from '#/main/app/overlays/tooltip/components/overlay'
import {getRule, getLabel, getClassList} from '#/plugin/flashcard/resources/flashcard/utils'

const SessionStep = props => {
  const rule = getRule(props.index)
  const label = getLabel(props.index, props.session, props.started, props.completed)
  const classList = getClassList(props.index, props.session, props.started, props.completed)

  return (
    <li className={classes(classList)}>
      <TooltipOverlay
        id={`session-${props.index}`}
        position={'bottom'}
        tip={`Session ${props.index} : ${rule}.`}
      >
        <div className="flashcard-timeline-number">
          {label}
        </div>
      </TooltipOverlay>
    </li>
  )
}

SessionStep.propTypes = {
  session: T.number,
  index: T.number,
  started: T.bool,
  completed: T.bool
}

const Timeline = (props) =>
  <ul className="flashcard-timeline-steps">
    {Array.from({ length: 7 }, (_, index) => (
      <SessionStep
        key={index}
        index={index + 1}
        session={props.session}
        started={props.started}
        completed={props.completed}
      />
    ))}
  </ul>

Timeline.propTypes = {
  session: T.number,
  started: T.bool,
  completed: T.bool,
  end: T.bool
}

export {
  Timeline
}
