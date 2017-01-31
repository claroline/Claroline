import React, {PropTypes as T} from 'react'
import {tcex} from '../../utils/translate'

export const SolutionScore = props =>
  <span className="item-score">
    {tcex('solution_score', props.score, {'score': props.score})}
  </span>

SolutionScore.propTypes = {
  score: T.number.isRequired
}
