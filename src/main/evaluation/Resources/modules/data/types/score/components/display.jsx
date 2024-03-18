import React from 'react'
import {PropTypes as T} from 'prop-types'

import {EvaluationScore} from '#/main/evaluation/components/score'

const ScoreDisplay = props => {
  if (props.data) {
    return (
      <EvaluationScore
        score={props.data.current}
        scoreMax={props.data.total}
      />
    )
  }

  return '-'
}

ScoreDisplay.propTypes = {
  data: T.shape({
    current: T.number,
    total: T.number.isRequired
  })
}

export {
  ScoreDisplay
}
