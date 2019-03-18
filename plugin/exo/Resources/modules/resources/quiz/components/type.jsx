import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'
import {MenuButton} from '#/main/app/buttons/menu/components/button'


import {QUIZ_TYPES} from '#/plugin/exo/resources/quiz/types'

const QuizType = props => {
  const current = QUIZ_TYPES[props.type]

  if (current) {
    return (
      <div className="quiz-type-control">
        <MenuButton
          id="quiz-type"
          className="quiz-type form-control"
          menu={
            <ul role="menu" className="dropdown-menu dropdown-menu-full">
              {Object.keys(QUIZ_TYPES).map(typeName => (
                <li key={typeName} role="presentation">
                  <CallbackButton
                    className="quiz-type"
                    active={typeName === props.type}
                    callback={() => props.onChange(QUIZ_TYPES[typeName])}
                  >
                    <span className={QUIZ_TYPES[typeName].meta.icon} />

                    <div>
                      <h1>{QUIZ_TYPES[typeName].meta.label}</h1>
                      <p className="hidden-xs">{QUIZ_TYPES[typeName].meta.description}</p>
                    </div>
                  </CallbackButton>
                </li>
              ))}
            </ul>
          }
        >
          <span className={current.meta.icon} />

          <div>
            <h1>{current.meta.label}</h1>
            <p className="hidden-xs">{current.meta.description}</p>
          </div>
        </MenuButton>
      </div>
    )
  }

  return (
    <div className="quiz-type form-control">

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
