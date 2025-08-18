import React from 'react'
import {PropTypes as T} from 'prop-types'

import {WordsSolutions} from '#/plugin/exo/items/words/components/solutions'

const WordsExpectedAnswer = (props) =>
  <WordsSolutions
    className="words-paper"
    contentType={props.item.contentType}
    answers={props.item.solutions}
    showScore={props.showScore}
    hasExpectedAnswers={false}
  />

WordsExpectedAnswer.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    title: T.string.isRequired,
    description: T.string.isRequired,
    solutions: T.arrayOf(T.object),
    contentType: T.string.isRequired,
    hasExpectedAnswers: T.bool.isRequired
  }).isRequired,
  showScore: T.bool.isRequired
}

export {
  WordsExpectedAnswer
}
