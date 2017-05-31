import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

const ScoreGauge = props => {
  const userScore = Math.round(props.userScore * 100) / 100
  const pClass = 'p' + (Math.round((props.userScore / props.maxScore) * 100))
  const pObj = {}
  pObj[pClass] = props.userScore && Math.round(props.userScore) > 0
  
  return (
    <div className={classes(
      'score-gauge',
      'c100',
      props.size,
      pObj
    )}>
      <span>{ (userScore || 0 === userScore ? userScore+'' : '-') + '/' + props.maxScore }</span>

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
  maxScore: T.number.isRequired
}

ScoreGauge.defaultProps = {
  userScore: null
}

export {ScoreGauge}
