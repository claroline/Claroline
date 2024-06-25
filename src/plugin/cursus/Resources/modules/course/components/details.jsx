import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'
import {Routes} from '#/main/app/router/components/routes'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ContentTabs} from '#/main/app/content/components/tabs'

import {Course as CourseTypes, Session as SessionTypes} from '#/plugin/cursus/prop-types'
import {CourseAbout} from '#/plugin/cursus/course/components/about'
import {CourseParticipants} from '#/plugin/cursus/course/containers/participants'
import {CourseSessions} from '#/plugin/cursus/course/components/sessions'
import {CourseEvents} from '#/plugin/cursus/course/containers/events'
import {CoursePending} from '#/plugin/cursus/course/containers/pending'
import {SessionParticipants} from '#/plugin/cursus/session/containers/participants'
import isEmpty from 'lodash/isEmpty'
import {
  canSelfRegister,
  getCourseRegistration,
  getInfo,
  getSessionRegistration,
  isFull,
  isFullyRegistered
} from '#/plugin/cursus/utils'
import {MODAL_COURSE_REGISTRATION} from '#/plugin/cursus/course/modals/registration'
import {route as workspaceRoute} from '#/main/core/workspace/routing'

const CourseDetails = (props) =>
  <Fragment>
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

    <Routes
      path={props.path}
      routes={[
        {
          path: '',
          exact: true,
          render: () => {
            const activeSessionRegistration = props.activeSession ? getSessionRegistration(props.activeSession, props.registrations) : null
            const courseRegistration = getCourseRegistration(props.registrations)

            const registered = !isEmpty(activeSessionRegistration) || !isEmpty(courseRegistration)
            let selfRegistration = !registered
              && (!isEmpty(props.activeSession) || !isEmpty(props.availableSessions) || get(props.course, 'registration.pendingRegistrations'))

            if (props.activeSession) {
              selfRegistration = selfRegistration && canSelfRegister(props.course, props.activeSession, props.registrations)
            }

            return (
              <CourseAbout
                path={props.path}
                contextType={props.contextType}
                course={props.course}
                activeSession={props.activeSession}
                availableSessions={props.availableSessions}
                registrations={props.registrations}
                register={props.register}
                actions={[
                  {
                    name: 'self-register',
                    type: MODAL_BUTTON,
                    label: trans(!isEmpty(props.activeSession) && isFull(props.activeSession) ? 'register_waiting_list' : 'self_register', {}, 'actions'),
                    modal: [MODAL_COURSE_REGISTRATION, {
                      course: props.course,
                      session: props.activeSession,
                      available: props.availableSessions,
                      register: props.register
                    }],
                    primary: true,
                    size: 'lg',
                    displayed: selfRegistration
                  }, {
                    name: 'open',
                    size: 'lg',
                    type: CALLBACK_BUTTON,
                    label: trans('open-training', {}, 'actions'),
                    callback: () => {
                      const workspaceUrl = workspaceRoute(getInfo(props.course, props.activeSession, 'workspace'))
                      if (get(props.activeSession, 'registration.autoRegistration') && !isFullyRegistered(activeSessionRegistration)) {
                        props.register(props.course, props.activeSession.id).then(() => props.history.push(workspaceUrl))
                      } else {
                        props.history.push(workspaceUrl)
                      }
                    },
                    displayed: (isFullyRegistered(activeSessionRegistration)
                      || get(props.activeSession, 'registration.autoRegistration')
                      || hasPermission('edit', props.course))
                      && !isEmpty(getInfo(props.course, props.activeSession, 'workspace'))
                      && props.contextType !== 'workspace',
                    primary: isFullyRegistered(activeSessionRegistration)
                  }, {
                    name: 'show-sessions',
                    type: LINK_BUTTON,
                    label: trans('show_sessions', {}, 'actions'),
                    target: props.path + '/sessions',
                    displayed: isEmpty(props.activeSession) && !get(props.course, 'display.hideSessions')
                  }, {
                    name: 'show-events',
                    type: LINK_BUTTON,
                    label: trans('show_training_events', {}, 'actions'),
                    target: props.path + '/events',
                    displayed: !isEmpty(props.activeSession)
                  }
                ]}
              />
            )
          }
        }, {
          path: '/sessions',
          disabled: get(props.course, 'display.hideSessions', false),
          render: () => (
            <CourseSessions
              contextType={props.contextType}
              path={props.path}
              course={props.course}
              registrations={props.registrations}
              reload={props.reload}
              register={props.register}
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
  availableSessions: T.arrayOf(T.shape(
    SessionTypes.propTypes
  )),
  contextType: T.string.isRequired,
  registrations: T.shape({
    users: T.array.isRequired,
    groups: T.array.isRequired,
    pending: T.array.isRequired
  }),
  participantsView: T.string.isRequired,
  switchParticipantsView: T.func.isRequired,
  register: T.func.isRequired
}

export {
  CourseDetails
}
