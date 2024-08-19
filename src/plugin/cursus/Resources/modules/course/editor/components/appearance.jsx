import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'
import {DataFormSection as DataFormSectionTypes} from '#/main/app/content/form/prop-types'

const CourseEditorAppearance = (props) =>
  <EditorPage
    title={trans('appearance')}
    help={trans('course_appearance_help', {}, 'cursus')}
    definition={[
      {
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters'),
        primary: true,
        fields: [
          {
            name: 'poster',
            label: trans('poster'),
            type: 'image'
          }, {
            name: 'thumbnail',
            label: trans('thumbnail'),
            type: 'image'
          },{
            name: 'display.order',
            type: 'number',
            label: trans('order'),
            required: true,
            options: {
              min: 0
            }
          },
          {
            name: 'opening.session',
            label: trans('opening_session', {}, 'cursus'),
            type: 'choice',
            required: true,
            options: {
              noEmpty: true,
              condensed: true,
              choices: {
                none: trans('opening_session_none', {}, 'cursus'),
                first_available: trans('opening_session_first_available', {}, 'cursus'),
                default: trans('opening_session_default', {}, 'cursus')
              }
            },
            help: trans('opening_session_help', {}, 'cursus')
          }, {
            name: 'display.hideSessions',
            type: 'boolean',
            label: trans('hide_sessions', {}, 'cursus')
          }, {
            name: 'restrictions.hidden',
            type: 'boolean',
            label: trans('restrict_hidden'),
            help: trans('restrict_hidden_help')
          }
        ]
      }
    ].concat(props.definition)}
  >
    {props.children}
  </EditorPage>

CourseEditorAppearance.propTypes = {
  definition: T.arrayOf(T.shape(
    DataFormSectionTypes.propTypes
  )),
  children: T.any
}

CourseEditorAppearance.defaultProps = {
  definition: []
}

export {
  CourseEditorAppearance
}
