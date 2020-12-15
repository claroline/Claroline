import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Highlight} from '#/plugin/exo/items/words/components/highlight'

export const WordsFeedback = props =>
  props.answer && 0 !== props.answer.length ?
    <Highlight
      className="words-feedback"
      text={props.answer}
      solutions={props.item.solutions}
      contentType={props.item.contentType}
      showScore={false}
      hasExpectedAnswers={props.item.hasExpectedAnswers}
    /> :
    <div className="no-answer">{trans('no_answer', {}, 'quiz')}</div>

WordsFeedback.propTypes = {
  item: T.shape({
    contentType: T.string.isRequired,
    solutions: T.arrayOf(T.object).isRequired,
    hasExpectedAnswers: T.bool.isRequired
  }).isRequired,
  answer: T.string
}
