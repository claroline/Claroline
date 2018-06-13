import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

const ScoreGauge = props => {
  let userScore = props.userScore
  let fillClass
  if (null !== userScore) {
    userScore = Math.round(props.userScore * 100) / 100
    fillClass = 'p' + (Math.round((userScore / props.maxScore) * 100))
  }
  
  return (
    <div className={classes('score-gauge c100', props.size, fillClass)}>
      <span>{(userScore || 0 === userScore ? userScore+'' : '-') + '/' + (props.maxScore ? props.maxScore : '-')}</span>

      <div className="slice" role="presentation">
        <div className="bar" role="presentation"></div>
        <div className="fill" role="presentation"></div>
      </div>
    </div>
  )
}

ScoreGauge.propTypes = {
  size: T.oneOf(['sm']),
  userScore: T.number,
  maxScore: T.number
}

ScoreGauge.defaultProps = {
  userScore: null,
  maxScore: null
}

export {ScoreGauge}
