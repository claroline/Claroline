import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ScoreBox} from '#/main/core/layout/evaluation/components/score-box'

const ScoreCell = props => {
  if (props.data) {
    return (
      <ScoreBox
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
