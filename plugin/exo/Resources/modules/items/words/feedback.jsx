import React, {PropTypes as T} from 'react'

import {tex} from '../../utils/translate'
import {Highlight} from './utils/highlight.jsx'

export const WordsFeedback = props =>
  props.answer && 0 !== props.answer.length ?
    <Highlight
      className="words-feedback"
      text={props.answer}
      solutions={props.item.solutions}
      showScore={false}
    /> :
    <div className="no-answer">{tex('no_answer')}</div>

WordsFeedback.propTypes = {
  item: T.shape({
    solutions: T.arrayOf(T.object).isRequired
  }).isRequired,
  answer: T.string
}
