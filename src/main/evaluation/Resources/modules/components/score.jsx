import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {number} from '#/main/app/intl'

const EvaluationScore = props => {
  let userScore = props.score
  if (null !== userScore && props.display) {
    userScore = (userScore / props.scoreMax) * props.display
  }

  return (
    <div className={classes(
      'score-box',
      props.className,
      props.size ? 'score-box-'+props.size : null
    )} style={props.style}>
      <span className="user-score">{userScore || 0 === userScore ? number(userScore) : '-'}</span>
      <span className="sr-only">/</span>

      {props.display ?
        <span className="max-score">{number(props.display)}</span>
        :
        <span className="max-score">{props.scoreMax || 0 === props.scoreMax ? number(props.scoreMax) : '-'}</span>
      }
    </div>
  )
}

EvaluationScore.propTypes = {
  style: T.object,
  score: T.number,
  scoreMax: T.number.isRequired,
  display: T.number,
  size: T.oneOf(['sm', 'md', 'lg']),
  className: T.string
}

EvaluationScore.defaultProps = {
  score: null
}

export {
  EvaluationScore
}
