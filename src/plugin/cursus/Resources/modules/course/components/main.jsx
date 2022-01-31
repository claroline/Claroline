import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {Routes} from '#/main/app/router/components/routes'
import {route} from '#/plugin/cursus/routing'
import {hasPermission} from '#/main/app/security'
import {Course as CourseTypes, Session as SessionTypes} from '#/plugin/cursus/prop-types'
import {CourseDetails} from '#/plugin/cursus/course/components/details'
import {CourseForm} from '#/plugin/cursus/course/containers/form'

// should be moved inside the current module instead
import {selectors} from '#/plugin/cursus/tools/trainings/catalog/store'

const CourseMain = (props) =>
  <Routes
    path={route(props.path, props.course)}
    redirect={[
      {from: '/', exact: true, to: '/'+get(props.defaultSession, 'id'), disabled: !props.defaultSession}
    ]}
    routes={[
      {
        path: '/',
        exact: true,
        disabled: !!props.defaultSession,
        onEnter: () => props.openSession(null),
        render: () => (
          <CourseDetails
            path={props.path}
            course={props.course}
            activeSession={null}
            activeSessionRegistration={null}
            availableSessions={props.availableSessions}
            courseRegistration={props.courseRegistration}
            isAuthenticated={props.isAuthenticated}
          />
        )
      }, {
        path: '/edit',
        onEnter: () => props.openForm(props.course.slug),
        disabled: !hasPermission('edit', props.course),
        render: () => (
          <CourseForm
            path={props.path}
            name={selectors.FORM_NAME}
          />
        )
      }, {
        path: '/:id',
        onEnter: (params = {}) => props.openSession(params.id),
        render: () => (
          <CourseDetails
            path={props.path}
            course={props.course}
            activeSession={props.activeSession}
            activeSessionRegistration={props.activeSessionRegistration}
            availableSessions={props.availableSessions}
            courseRegistration={props.courseRegistration}
            isAuthenticated={props.isAuthenticated}
          />
        )
      }
    ]}
  />

CourseMain.propTypes = {
  path: T.string.isRequired,
  isAuthenticated: T.bool.isRequired,
  course: T.shape(
    CourseTypes.propTypes
  ).isRequired,
  defaultSession: T.shape(
    SessionTypes.propTypes
  ),
  activeSession: T.shape(
    SessionTypes.propTypes
  ),
  availableSessions: T.arrayOf(T.shape(
    SessionTypes.propTypes
  )),
  activeSessionRegistration: T.shape({
    // TODO : propTypes
  }),
  courseRegistration: T.shape({}),
  openSession: T.func.isRequired,
  openForm: T.func.isRequired
}

export {
  CourseMain
}