import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {utils} from '#/plugin/exo/items/words/utils'
import {WordsSolutions} from '#/plugin/exo/items/words/components/solutions'

const WordsAnswer = props => {
  if (isEmpty(props.text)) {
    return (
      <div className="no-answer">{trans('no_answer', {}, 'quiz')}</div>
    )
  }

  const foundSolutions = utils.findSolutions(props.text, props.solutions)

  return (
    <div className={props.className}>
      <p dangerouslySetInnerHTML={{__html: utils.highlight(props.text, props.contentType, foundSolutions, props.hasExpectedAnswers)}} />

      <WordsSolutions
        contentType={props.contentType}
        answers={foundSolutions}
        showScore={props.showScore}
        hasExpectedAnswers={props.hasExpectedAnswers}
      />
    </div>
  )
}

WordsAnswer.propTypes = {
  text: T.string,
  contentType: T.string.isRequired,
  solutions: T.array.isRequired,
  showScore: T.bool.isRequired,
  hasExpectedAnswers: T.bool.isRequired,
  className: T.string
}

export {
  WordsAnswer
}
