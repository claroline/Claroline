import React, {PropTypes as T} from 'react'
import classes from 'classnames'
import {tex} from '../../utils/translate'

export const ScoreBox = props =>
  <span className={classes('score-box', props.className)}>
    {tex('score')}: {props.score + (props.scoreMax ? ' / ' + props.scoreMax : '')}
  </span>

ScoreBox.propTypes = {
  score: T.any,
  scoreMax: T.any,
  className: T.string
}
