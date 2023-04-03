import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {withRouter} from '#/main/app/router'
import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'
import {Routes} from '#/main/app/router/components/routes'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentTabs} from '#/main/app/content/components/tabs'

import {Course as CourseTypes, Session as SessionTypes} from '#/plugin/cursus/prop-types'
import {CourseAbout} from '#/plugin/cursus/course/containers/about'
import {CourseParticipants} from '#/plugin/cursus/course/containers/participants'
import {CourseSessions} from '#/plugin/cursus/course/containers/sessions'
import {CourseEvents} from '#/plugin/cursus/course/containers/events'
import {CoursePending} from '#/plugin/cursus/course/containers/pending'
import {SessionParticipants} from '#/plugin/cursus/session/containers/participants'

const CourseDetails = (props) =>
  <Fragment>
    <header className="row content-heading">
      <ContentTabs
        sections={[
          {
            name: 'about',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-circle-info',
            label: props.activeSession ? trans('session_about', {}, 'cursus') : trans('about'),
            target: props.path,
            exact: true
          }, {
            name: 'sessions',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-calendar-week',
            label: trans('sessions', {}, 'cursus'),
            target: `${props.path}/sessions`,
            displayed: !get(props.course, 'display.hideSessions')
          }, {
            name: 'pending',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-hourglass-half',
            label: trans('En attente'),
            displayed: hasPermission('register', props.course) && get(props.course, 'registration.pendingRegistrations'),
            target: `${props.path}/pending`
          }, {
            name: 'participants',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-users',
            label: trans('participants'),
            target: `${props.path}/participants`,
            displayed: hasPermission('register', props.course) || (props.activeSession && hasPermission('register', props.activeSession))
          }, {
            name: 'events',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-clock',
            label: trans('session_events', {}, 'cursus'),
            target: `${props.path}/events`,
            displayed: !!props.activeSession
          }
        ]}
      />
    </header>

    <Routes
      path={props.path}
      routes={[
        {
          path: '',
          exact: true,
          render: () => (
            <CourseAbout
              path={props.path}
              course={props.course}
              activeSession={props.activeSession}
              activeSessionRegistration={props.activeSessionRegistration}
              courseRegistration={props.courseRegistration}
              availableSessions={props.availableSessions}
            />
          )
        }, {
          path: '/sessions',
          disabled: get(props.course, 'display.hideSessions', false),
          render: () => (
            <CourseSessions
              path={props.path}
              course={props.course}
            />
          )
        }, {
          path: '/pending',
          disabled: !hasPermission('register', props.course) || !get(props.course, 'registration.pendingRegistrations'),
          render: () => (
            <CoursePending
              course={props.course}
            />
          )
        }, {
          path: '/participants',
          disabled: !hasPermission('register', props.course) || (props.activeSession && !hasPermission('register', props.activeSession)),
          onEnter: () => {
            if (!props.activeSession) {
              props.switchParticipantsView('course')
            }
          },
          render: () => {
            if ('session' === props.participantsView) {
              return (
                <SessionParticipants
                  path={props.path+'/participants'}
                  course={props.course}
                  activeSession={props.activeSession}
                  toggleVisibility={() => props.switchParticipantsView('course')}
                />
              )
            }

            return (
              <CourseParticipants
                path={props.path+'/participants'}
                course={props.course}
                activeSession={props.activeSession}
                toggleVisibility={() => props.switchParticipantsView('session')}
              />
            )
          }
        }, {
          path: '/events',
          disabled: !props.activeSession,
          render: () => (
            <CourseEvents
              path={props.path}
              course={props.course}
              activeSession={props.activeSession}
            />
          )
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
  courseRegistration: T.shape({

  }),
  availableSessions: T.arrayOf(T.shape(
    SessionTypes.propTypes
  )),
  participantsView: T.string.isRequired,
  switchParticipantsView: T.func.isRequired
}

const RoutedCourse = withRouter(CourseDetails)

export {
  RoutedCourse as CourseDetails
}
