import React, {PropTypes as T} from 'react'
import {Highlight} from './utils/highlight.jsx'

export const ClozeFeedback = props => {
  return (
    <Highlight
      item={props.item}
      showScore={false}
      answer={props.answer}
      displayTrueAnswer={false}
    />
  )
}

ClozeFeedback.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    solutions: T.arrayOf(T.object)
  }).isRequired,
  answer: T.array.isRequired
}

ClozeFeedback.defaultProps = {
  answer: []
}
