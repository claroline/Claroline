import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'

import {selectors} from '#/plugin/cursus/course/store'
import {Course as CourseTypes} from '#/plugin/cursus/prop-types'
import {SessionUsers} from '#/plugin/cursus/session/components/users'

const CourseUsers = (props) =>
  <SessionUsers
    className="mt-4"
    path={props.path}
    url={['apiv2_training_session_user_course_list', {id: props.course.id}]}
    name={selectors.STORE_NAME+'.sessionUsers'}
    registrationForm={get(props.course, 'registration.form')}
    confirmation={get(props.course, 'registration.userValidation', false)}
    validation={get(props.course, 'registration.validation', false)}
    customDefinition={[
      {
        name: 'session',
        label: trans('session', {}, 'cursus'),
        type: 'training_session',
        displayed: true,
        displayable: true,
        filterable: true,
        options: {
          picker: {
            url: ['apiv2_cursus_course_list_sessions', {id: get(props.course, 'id')}]
          }
        }
      }, {
        name: 'sessionStatus',
        type: 'choice',
        label: trans('status'),
        order: 2,
        displayable: false,
        sortable: false,
        filterable: true,
        options: {
          noEmpty: true,
          choices: {
            no_session: trans('no_session', {}, 'cursus'),
            not_started: trans('session_not_started', {}, 'cursus'),
            in_progress: trans('session_in_progress', {}, 'cursus'),
            ended: trans('session_ended', {}, 'cursus'),
            not_ended: trans('session_not_ended', {}, 'cursus')
          }
        }
      },
    ]}
  />

CourseUsers.propTypes = {
  path: T.string.isRequired,
  course: T.shape(
    CourseTypes.propTypes
  ).isRequired
}

export {
  CourseUsers
}
