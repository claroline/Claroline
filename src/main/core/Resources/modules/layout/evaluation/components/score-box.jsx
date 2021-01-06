import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {transChoice} from '#/main/app/intl/translation'

const ScoreBox = props => {
  let userScore = props.score
  if (null !== userScore) {
    userScore = Math.round(userScore * 100) / 100
  }

  return (
    <div className={classes(
      'score-box',
      props.className,
      props.size ? 'score-box-'+props.size : null
    )} style={props.style}>
      <span className="user-score">{userScore || 0 === userScore ? userScore : '-'}</span>
      <span className="sr-only">/</span>
      <span className="max-score">{props.scoreMax || 0 === props.scoreMax ? transChoice('points', props.scoreMax, {count: props.scoreMax}) : '-'}</span>
    </div>
  )
}

ScoreBox.propTypes = {
  style: T.object,
  score: T.number,
  scoreMax: T.number.isRequired,
  size: T.oneOf(['sm', 'lg']),
  className: T.string
}

ScoreBox.defaultProps = {
  score: null
}

export {
  ScoreBox
}
