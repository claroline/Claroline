import React from 'react'
import {PropTypes as T} from 'prop-types'

const AnswerStats = props =>
  <span className="answer-stats">
    {props.stats.value && props.stats.total ? Math.round((props.stats.value /  props.stats.total) * 100) : 0} %
    &nbsp;
    ({props.stats.value} / {props.stats.total})
  </span>

AnswerStats.propTypes = {
  stats: T.shape({
    value: T.number.isRequired,
    total: T.number.isRequired
  }).isRequired
}

export {
  AnswerStats
}
