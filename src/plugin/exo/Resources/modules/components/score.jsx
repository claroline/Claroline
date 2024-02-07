import React from 'react'
import {PropTypes as T} from 'prop-types'

import {transChoice} from '#/main/app/intl/translation'

const SolutionScore = props =>
  <span className="solution-score badge">
    {transChoice('solution_score', props.score, {score: props.score}, 'quiz')}
  </span>

SolutionScore.propTypes = {
  score: T.number.isRequired
}

export {
  SolutionScore
}
