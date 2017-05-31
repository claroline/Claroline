import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import {transChoice} from '#/main/core/translation'

export const ScoreBox = props =>
  <div className={classes(
    'score-box',
    props.className,
    props.size ? 'score-box-'+props.size : null
  )}>
    <span className="user-score">{Math.round(props.score * 100) / 100}</span>
    <span className="sr-only">/</span>
    <span className="max-score">{transChoice('item_points', props.scoreMax, {count: props.scoreMax}, 'ujm_exo')}</span>
  </div>

ScoreBox.propTypes = {
  score: T.number.isRequired,
  scoreMax: T.number.isRequired,
  size: T.oneOf(['sm']),
  className: T.string
}
