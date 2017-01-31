import React, {PropTypes as T} from 'react'
import {Highlight} from './utils/highlight.jsx'

export const WordsFeedback = props => {
  return (
    <Highlight
      text={props.answer}
      solutions={props.item.solutions}
      showScore={false}
    />
  )
}

WordsFeedback.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    solutions: T.arrayOf(T.object)
  }).isRequired,
  answer: T.string
}
