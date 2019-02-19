import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'

import {QUIZ_TYPES} from '#/plugin/exo/resources/quiz/types'

const QuizType = props => {
  const current = QUIZ_TYPES[props.type]

  return (
    <div className="quiz-type form-control">
      <span className={current.meta.icon} />

      <div>
        <h1>{current.meta.label}</h1>
        <p className="hidden-xs">{current.meta.description}</p>
      </div>
    </div>
  )
}

QuizType.propTypes = {
  type: T.string,
  onChange: T.func.isRequired
}

export {
  QuizType
}
