import React, {PropTypes as T} from 'react'
import classes from 'classnames'

const ScoreGauge = props =>
  <div className={classes(
    'score-gauge',
    'c100',
    props.size,
    'p'+(props.userScore ? Math.round((props.userScore / props.maxScore) * 100) : 0)
  )}>
    <span>{ (props.userScore || 0 === props.userScore ? props.userScore+'' : '-') + '/' + props.maxScore }</span>

    <div className="slice" role="presentation">
      <div className="bar" role="presentation"></div>
      <div className="fill" role="presentation"></div>
    </div>
  </div>

ScoreGauge.propTypes = {
  size: T.oneOf(['sm']),
  userScore: T.number,
  maxScore: T.number.isRequired
}

ScoreGauge.defaultProps = {
  userScore: null
}

export {ScoreGauge}
