import React from 'react'
import {PropTypes as T} from 'prop-types'

import {tex} from '#/main/app/intl/translation'
import {Highlight} from '#/plugin/exo/items/words/components/highlight'

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
