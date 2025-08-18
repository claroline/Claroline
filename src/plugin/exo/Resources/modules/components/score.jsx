import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {transChoice} from '#/main/app/intl/translation'

const SolutionScore = props =>
  <span className={classes('solution-score badge', props.className)}>
    {transChoice('solution_score', props.score, {score: props.score}, 'quiz')}
  </span>

SolutionScore.propTypes = {
  className: T.string,
  score: T.number.isRequired
}

export {
  SolutionScore
}
