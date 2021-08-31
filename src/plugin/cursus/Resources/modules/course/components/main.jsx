import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {Routes} from '#/main/app/router/components/routes'

import {route} from '#/plugin/cursus/routing'
import {Course as CourseTypes, Session as SessionTypes} from '#/plugin/cursus/prop-types'
import {CourseDetails} from '#/plugin/cursus/course/components/details'

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
          />
        )
      }
    ]}
  />

CourseMain.propTypes = {
  path: T.string.isRequired,
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
  openSession: T.func.isRequired
}

export {
  CourseMain
}