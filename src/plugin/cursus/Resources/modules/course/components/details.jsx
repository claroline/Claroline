import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {Routes} from '#/main/app/router/components/routes'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentTabs} from '#/main/app/content/components/tabs'

import {
  Course as CourseTypes,
  Session as SessionTypes
} from '#/plugin/cursus/prop-types'
import {route} from '#/plugin/cursus/routing'
import {CourseAbout} from '#/plugin/cursus/course/components/about'
import {CourseParticipants} from '#/plugin/cursus/course/containers/participants'
import {CourseSessions} from '#/plugin/cursus/course/containers/sessions'
import {CourseEvents} from '#/plugin/cursus/course/containers/events'

const CourseDetails = (props) =>
  <Fragment>
    <header className="row content-heading">
      <ContentTabs
        backAction={{
          type: LINK_BUTTON,
          target: props.path,
          exact: true
        }}
        sections={[
          {
            name: 'about',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-info',
            label: props.activeSession ? trans('session_about', {}, 'cursus') : trans('about'),
            target: route(props.path, props.course, props.activeSession),
            exact: true
          }, {
            name: 'sessions',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-calendar-week',
            label: trans('sessions', {}, 'cursus'),
            target: `${route(props.path, props.course, props.activeSession)}/sessions`
          }, {
            name: 'participants',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-users',
            label: trans('participants'),
            target: `${route(props.path, props.course, props.activeSession)}/participants`,
            displayed: !!props.activeSession
          }, {
            name: 'events',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-clock',
            label: trans('session_events', {}, 'cursus'),
            target: `${route(props.path, props.course, props.activeSession)}/events`,
            displayed: !!props.activeSession
          }
        ]}
      />
    </header>

    <Routes
      path={route(props.path, props.course, props.activeSession)}
      routes={[
        {
          path: '/',
          exact: true,
          render() {
            return (
              <CourseAbout
                path={props.path}
                course={props.course}
                activeSession={props.activeSession}
                activeSessionRegistration={props.activeSessionRegistration}
                availableSessions={props.availableSessions}
                register={props.register}
              />
            )
          }
        }, {
          path: '/sessions',
          render() {
            return (
              <CourseSessions
                path={props.path}
                course={props.course}
              />
            )
          }
        }, {
          path: '/participants',
          disabled: !props.activeSession,
          render() {
            return (
              <CourseParticipants
                path={props.path}
                course={props.course}
                activeSession={props.activeSession}
              />
            )
          }
        }, {
          path: '/events',
          disabled: !props.activeSession,
          render() {
            return (
              <CourseEvents
                path={props.path}
                course={props.course}
                activeSession={props.activeSession}
              />
            )
          }
        }
      ]}
    />
  </Fragment>

CourseDetails.propTypes = {
  path: T.string.isRequired,
  course: T.shape(
    CourseTypes.propTypes
  ).isRequired,
  activeSession: T.shape(
    SessionTypes.propTypes
  ),
  activeSessionRegistration: T.shape({

  }),
  availableSessions: T.arrayOf(T.shape(
    SessionTypes.propTypes
  )),
  register: T.func.isRequired
}

export {
  CourseDetails
}
