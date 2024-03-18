import React from 'react'
import {PropTypes as T} from 'prop-types'

import {EvaluationScore} from '#/main/evaluation/components/score'

const ScoreCell = props => {
  if (props.data) {
    return (
      <EvaluationScore
        size="sm"
        score={props.data.current}
        scoreMax={props.data.total}
      />
    )
  }

  return '-'
}

ScoreCell.propTypes = {
  data: T.shape({
    current: T.number,
    total: T.number.isRequired
  })
}

export {
  ScoreCell
}
