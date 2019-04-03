import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors} from '#/main/core/resource/modals/creation/store'

import {QuizType} from '#/plugin/exo/resources/quiz/components/type'

const QuizCreation = props =>
  <FormData
    level={5}
    name={selectors.STORE_NAME}
    dataPart={selectors.FORM_RESOURCE_PART}
    embedded={true}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'parameters.type',
            label: trans('type'),
            type: 'string',
            required: true,
            render: (quiz) => {
              const CurrentType = (
                <QuizType
                  type={get(quiz, 'parameters.type')}
                  selectAction={(type) => ({
                    type: CALLBACK_BUTTON,
                    callback: () => props.changeType(type)
                  })}
                />
              )

              return CurrentType
            }
          }
        ]
      }
    ]}
  />

QuizCreation.propTypes = {
  changeType: T.func.isRequired
}

export {
  QuizCreation
}
