import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'
import {MenuButton} from '#/main/app/buttons/menu/components/button'

import {QUIZ_TYPES} from '#/plugin/exo/resources/quiz/types'

const CurrentType = props =>
  <Fragment>
    {props.icon &&
      <span className={props.icon} />
    }

    <div>
      <h1>{props.label}</h1>

      {props.description &&
        <p className="hidden-xs">{props.description}</p>
      }
    </div>
  </Fragment>

CurrentType.propTypes = {
  icon: T.string,
  label: T.string.isRequired,
  description: T.string
}

const QuizType = props => {
  const current = QUIZ_TYPES[props.type]

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
                  <CurrentType
                    icon={QUIZ_TYPES[typeName].meta.icon}
                    label={QUIZ_TYPES[typeName].meta.label}
                    description={QUIZ_TYPES[typeName].meta.description}
                  />
                </CallbackButton>
              </li>
            ))}
          </ul>
        }
      >
        {current &&
          <CurrentType
            icon={current.meta.icon}
            label={current.meta.label}
            description={current.meta.description}
          />
        }

        {!current &&
          <CurrentType
            label={trans('select_quiz_type', {}, 'quiz')}
            description={trans('select_quiz_type_help', {}, 'quiz')}
          />
        }
      </MenuButton>
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
