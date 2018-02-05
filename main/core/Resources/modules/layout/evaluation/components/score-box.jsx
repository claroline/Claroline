import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {transChoice} from '#/main/core/translation'

const ScoreBox = props =>
  <div className={classes(
    'score-box',
    props.className,
    props.size ? 'score-box-'+props.size : null
  )}>
    <span className="user-score">{Math.round(props.score * 100) / 100}</span>
    <span className="sr-only">/</span>
    <span className="max-score">{transChoice('points', props.scoreMax, {count: props.scoreMax})}</span>
  </div>

ScoreBox.propTypes = {
  score: T.number.isRequired,
  scoreMax: T.number.isRequired,
  size: T.oneOf(['sm', 'lg']),
  className: T.string
}

export {
  ScoreBox
}
