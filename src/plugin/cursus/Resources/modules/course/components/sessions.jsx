import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {Button} from '#/main/app/action/components/button'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {Course as CourseTypes} from '#/plugin/cursus/prop-types'
import {route} from '#/plugin/cursus/routing'
import {MODAL_SESSION_FORM} from '#/plugin/cursus/session/modals/parameters'
import {SessionList} from '#/plugin/cursus/session/components/list'
import {selectors} from '#/plugin/cursus/tools/trainings/catalog/store/selectors'

const CourseSessions = (props) =>
  <Fragment>
    <SessionList
      path={props.path}
      name={selectors.STORE_NAME+'.courseSessions'}
      url={['apiv2_cursus_course_list_sessions', {id: props.course.id}]}
      delete={{
        url: ['apiv2_cursus_session_delete_bulk']
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
          name: 'edit',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-pencil',
          label: trans('edit', {}, 'actions'),
          modal: [MODAL_SESSION_FORM, {
            session: rows[0],
            onSave: () => props.reload(props.course.slug)
          }],
          scope: ['object'],
          group: trans('management')
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
  reload: T.func.isRequired
}

export {
  CourseSessions
}
