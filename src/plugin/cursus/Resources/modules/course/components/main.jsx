import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {Routes} from '#/main/app/router/components/routes'

import {route} from '#/plugin/cursus/routing'
import {Course as CourseTypes} from '#/plugin/cursus/prop-types'
import {CourseDetails} from '#/plugin/cursus/course/components/details'

const CourseMain = (props) =>
  <Routes
    path={route(props.path, props.course)}
    redirect={[
      {from: '/', exact: true, to: '/'+get(props.activeSession, 'id'), disabled: !props.activeSession}
    ]}
    routes={[
      {
        path: '/',
        disabled: !!props.activeSession,
        render: () => (
          <CourseDetails
            path={props.path}
            course={props.course}
            availableSessions={props.availableSessions}
            register={props.register}
          />
        )
      }, {
        path: '/:id',
        onEnter(params = {}) {
          if (params.id) {
            props.openSession(params.id)
          }
        },
        render: () => (
          <CourseDetails
            path={props.path}
            course={props.course}
            activeSession={props.activeSession}
            activeSessionRegistration={props.activeSessionRegistration}
            availableSessions={props.availableSessions}
            register={props.register}
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
  activeSession: T.shape({
    id: T.string.isRequired
  }),
  availableSessions: T.arrayOf(T.shape({
    // TODO : propTypes
  })),
  activeSessionRegistration: T.shape({
    // TODO : propTypes
  }),
  openSession: T.func.isRequired,
  register: T.func.isRequired
}

export {
  CourseMain
}