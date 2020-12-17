import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

export const WarningIcon = props => {
  if (props.answers && props.answers.indexOf(props.solution.id) > -1) {
    return props.solution.score > 0 ?
       <span className={classes('answer-warning-span fa fa-check', props.className)} aria-hidden="true" /> :
       <span className={classes('answer-warning-span fa fa-times', props.className)} aria-hidden="true" />
  }

  return <span className={classes('answer-warning-span', props.className)} />
}

WarningIcon.propTypes = {
  answers: T.array,
  solution: T.shape({
    score: T.number,
    id: T.string
  }),
  className: T.string
}
