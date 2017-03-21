import React, {PropTypes as T} from 'react'
import classes from 'classnames'
import {tcex} from '../../utils/translate'

export const ScoreBox = props =>
  <div className={classes(
    'score-box',
    props.className,
    props.size ? 'score-box-'+props.size : null
  )}>
    <span className="user-score">{props.score}</span>
    <span className="sr-only">/</span>
    <span className="max-score">{tcex('item_points', props.scoreMax, {count: props.scoreMax})}</span>
  </div>

ScoreBox.propTypes = {
  score: T.number.isRequired,
  scoreMax: T.number.isRequired,
  size: T.oneOf(['sm']),
  className: T.string
}
