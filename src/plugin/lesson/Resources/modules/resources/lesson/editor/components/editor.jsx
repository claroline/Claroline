import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {constants} from '#/plugin/lesson/resources/lesson/constants'
import {selectors} from '#/plugin/lesson/resources/lesson/editor/store'

const Editor = (props) =>
  <FormData
    level={2}
    name={selectors.STORE_NAME}
    title={trans('parameters')}
    buttons={true}
    save={{
      type: CALLBACK_BUTTON,
      callback: () => props.saveForm(props.lesson.id)
    }}
    cancel={{
      type: LINK_BUTTON,
      target: props.path,
      exact: true
    }}
    sections={[
      {
        icon: 'fa fa-fw fa-home',
        title: trans('overview'),
        fields: [
          {
            name: 'display.showOverview',
            type: 'boolean',
            label: trans('enable_overview'),
            linked: [
              {
                name: 'display.description',
                type: 'html',
                label: trans('overview_message'),
                displayed: (lesson) => get(lesson, 'display.showOverview', false),
                options: {
                  workspace: props.workspace
                }
              }
            ]
          }
        ]
      }, {
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters'),
        fields: [
          {
            name: 'display.numbering',
            type: 'choice',
            label: trans('lesson_numbering', {}, 'lesson'),
            required: true,
            options: {
              noEmpty: true,
              condensed: true,
              choices: constants.LESSON_NUMBERINGS
            }
          }
        ]
      }
    ]}
  />

Editor.propTypes = {
  path: T.string.isRequired,
  workspace: T.object,
  lesson: T.object,
  saveForm: T.func.isRequired
}

export {
  Editor
}
