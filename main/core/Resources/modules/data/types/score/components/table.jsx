import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ScoreBox} from '#/main/core/layout/evaluation/components/score-box.jsx'

const ScoreTable = props =>
  <ScoreBox
    size="sm"
    score={props.data}
    scoreMax={props.max}
  />

ScoreTable.propTypes = {
  data: T.number,
  max: T.number.isRequired
}

export {
  ScoreTable
}
