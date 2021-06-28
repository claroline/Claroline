import React from 'react'
import {PropTypes as T} from 'prop-types'

import {WordsAnswer} from '#/plugin/exo/items/words/components/answer'

const WordsFeedback = props =>
  <WordsAnswer
    className="words-feedback"
    text={props.answer}
    solutions={props.item.solutions}
    contentType={props.item.contentType}
    showScore={false}
    hasExpectedAnswers={props.item.hasExpectedAnswers}
  />

WordsFeedback.propTypes = {
  item: T.shape({
    contentType: T.string.isRequired,
    solutions: T.arrayOf(T.object).isRequired,
    hasExpectedAnswers: T.bool.isRequired
  }).isRequired,
  answer: T.string
}

export {
  WordsFeedback
}
