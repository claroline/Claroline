import React, {PropTypes as T} from 'react'

export const WarningIcon = props => {
  if (props.answers && props.answers.indexOf(props.solution.id) > -1) {
    return props.solution.score > 0 ?
       <span className="fa fa-check answer-warning-span" aria-hidden="true"></span> :
       <span className="fa fa-times answer-warning-span" aria-hidden="true"></span>
  }

  return <span className="answer-warning-span"></span>
}

WarningIcon.propTypes = {
  answers: T.array,
  solution: T.shape({
    score: T.number,
    id: T.string
  })
}
