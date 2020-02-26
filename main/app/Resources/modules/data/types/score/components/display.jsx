import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ScoreBox} from '#/main/core/layout/evaluation/components/score-box'

const ScoreDisplay = props => {
  if (props.data) {
    return (
      <ScoreBox
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
