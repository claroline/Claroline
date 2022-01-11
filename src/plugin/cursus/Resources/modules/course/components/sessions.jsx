import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {route as workspaceRoute} from '#/main/core/workspace/routing'

import {Course as CourseTypes} from '#/plugin/cursus/prop-types'
import {route} from '#/plugin/cursus/routing'
import {MODAL_SESSION_FORM} from '#/plugin/cursus/session/modals/parameters'
import {SessionList} from '#/plugin/cursus/session/components/list'
import {selectors} from '#/plugin/cursus/tools/trainings/catalog/store/selectors'
import {getInfo, isRegistered, isFull} from '#/plugin/cursus/utils'
import {MODAL_COURSE_REGISTRATION} from '#/plugin/cursus/course/modals/registration'

function canSelfRegister(course, session, registrations) {
  return getInfo(course, session, 'registration.selfRegistration')
    && !getInfo(course, session, 'registration.autoRegistration')
    && !isRegistered(session, registrations)
    && (getInfo(course, session, 'registration.pendingRegistrations') || !isFull(session))
}

const CourseSessions = (props) =>
  <Fragment>
    <SessionList
      path={props.path}
      name={selectors.STORE_NAME+'.courseSessions'}
      url={['apiv2_cursus_course_list_sessions', {id: props.course.id}]}
      delete={{
        url: ['apiv2_cursus_session_delete_bulk'],
        displayed: (row) => hasPermission('delete', row)
      }}
      definition={[
        {
          name: 'meta.default',
          type: 'boolean',
          label: trans('default')
        }, {
          name: 'registration.selfRegistration',
          alias: 'publicRegistration',
          type: 'boolean',
          label: trans('public_registration')
        }, {
          name: 'registration.selfUnregistration',
          alias: 'publicUnregistration',
          type: 'boolean',
          label: trans('public_unregistration')
        }, {
          name: 'registration.validation',
          alias: 'registrationValidation',
          type: 'boolean',
          label: trans('registration_validation', {}, 'cursus')
        }, {
          name: 'registration.userValidation',
          alias: 'userValidation',
          type: 'boolean',
          label: trans('user_validation', {}, 'cursus')
        }
      ]}
      actions={(rows) => [
        {
          name: 'open-workspace',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-arrow-circle-o-right',
          label: trans('open-training', {}, 'actions'),
          displayed: !isEmpty(getInfo(props.course, rows[0], 'workspace')) && (hasPermission('edit', rows[0]) || getInfo(props.course, rows[0], 'registration.autoRegistration') || isRegistered(rows[0], props.registrations)),
          callback: () => {
            const workspaceUrl = workspaceRoute(getInfo(props.course, rows[0], 'workspace'))
            if (get(rows[0], 'registration.autoRegistration') && !isRegistered(rows[0], props.registrations)) {
              props.register(props.course, rows[0].id).then(() => props.history.push(workspaceUrl))
            } else {
              props.history.push(workspaceUrl)
            }
          },
          scope: ['object'],
          primary: true
        }, {
          name: 'edit',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-pencil',
          label: trans('edit', {}, 'actions'),
          modal: [MODAL_SESSION_FORM, {
            session: rows[0],
            onSave: () => props.reload(props.course.slug)
          }],
          scope: ['object'],
          displayed: hasPermission('edit', rows[0]),
          group: trans('management')
        }, {
          name: 'self-register',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-user-plus',
          label: trans(isFull(rows[0]) ? 'register_waiting_list' : 'self_register', {}, 'actions'),
          displayed: canSelfRegister(props.course, rows[0], props.registrations),
          modal: [MODAL_COURSE_REGISTRATION, {
            path: props.path,
            course: props.course,
            session: rows[0],
            register: props.register
          }],
          tooltip: null,
          scope: ['object']
        }
      ]}
    />

    {hasPermission('edit', props.course) &&
      <Button
        className="btn btn-block btn-emphasis component-container"
        type={MODAL_BUTTON}
        label={trans('add_session', {}, 'cursus')}
        modal={[MODAL_SESSION_FORM, {
          course: props.course,
          onSave: (newSession) => {
            // open created session, but let user on sessions list to allow multiples creations
            props.history.push(route(props.path, props.course, newSession)+'/sessions')
            props.reload(props.course.slug)
          }
        }]}
        primary={true}
      />
    }
  </Fragment>

CourseSessions.propTypes = {
  path: T.string.isRequired,
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  course: T.shape(
    CourseTypes.propTypes
  ).isRequired,
  registrations: T.shape({
    users: T.array,
    groups: T.array
  }),
  reload: T.func.isRequired,
  register: T.func.isRequired
}

export {
  CourseSessions
}
