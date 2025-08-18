import React from 'react'
import {PropTypes as T} from 'prop-types'

import isEmpty from 'lodash/isEmpty'
import {trans} from '#/main/app/intl'
import {utils} from '#/plugin/exo/items/words/utils'
import {WordsSolutions} from '#/plugin/exo/items/words/components/solutions'
import {Html} from '#/main/app/components/html'

const WordsFeedback = props => {
  if (isEmpty(props.answer)) {
    return (
      <div className="no-answer">{trans('no_answer', {}, 'quiz')}</div>
    )
  }

  const foundSolutions = utils.findSolutions(props.answer, props.item.solutions)

  return (
    <div className="words-feedback">
      <Html>
        {utils.highlight(props.answer, props.item.contentType, foundSolutions, props.item.hasExpectedAnswers)}
      </Html>

      <WordsSolutions
        contentType={props.item.contentType}
        answers={foundSolutions}
        showScore={props.showScore}
        hasExpectedAnswers={props.item.hasExpectedAnswers}
      />
    </div>
  )
}

WordsFeedback.propTypes = {
  item: T.shape({
    contentType: T.string.isRequired,
    solutions: T.arrayOf(T.object).isRequired,
    hasExpectedAnswers: T.bool.isRequired
  }).isRequired,
  showScore: T.bool,
  answer: T.string
}

export {
  WordsFeedback
}
