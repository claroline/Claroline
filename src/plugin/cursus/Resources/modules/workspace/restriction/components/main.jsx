import React, {useState} from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch} from 'react-redux'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {Router, Routes} from '#/main/app/router'
import {hasPermission} from '#/main/app/security'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {route} from '#/plugin/cursus/routing'
import {canSelfRegister, getCourseRegistration, getSessionRegistration, isFull} from '#/plugin/cursus/utils'
import {CourseAbout} from '#/plugin/cursus/course/components/about'
import {MODAL_COURSE_REGISTRATION} from '#/plugin/cursus/course/modals/registration'
import {actions} from '#/plugin/cursus/course/store'

const RestrictionMain = (props) => {
  const course = get(props.errors, 'trainings.course')
  const defaultSession = get(props.errors, 'trainings.defaultSession')
  const availableSessions = get(props.errors, 'trainings.availableSessions')

  const [activeSession, setActiveSession] = useState(defaultSession || null)

  const dispatch = useDispatch()

  const registrations = get(props.errors, 'trainings.registrations')

  return (
    <Router embedded={true} basename={route(course, null, props.path)}>
      <Routes
        path={route(get(props.errors, 'trainings.course'), null, props.path)}
        routes={[
          {
            path: '/:id?',
            onEnter: (params = {}) => {
              if (params.id) {
                setActiveSession(availableSessions.find(session => session.id === params.id))
              } else {
                setActiveSession(defaultSession || null)
              }
            },
            render: () => {
              const activeSessionRegistration = activeSession ? getSessionRegistration(activeSession, registrations) : null
              const courseRegistration = getCourseRegistration(registrations)

              const registered = !isEmpty(activeSessionRegistration) || !isEmpty(courseRegistration)
              let selfRegistration = !registered
                && (!isEmpty(activeSession) || !isEmpty(availableSessions) || get(course, 'registration.pendingRegistrations'))

              if (activeSession) {
                selfRegistration = selfRegistration && canSelfRegister(course, activeSession, registrations)
              }

              return (
                <CourseAbout
                  course={course}
                  activeSession={activeSession}
                  availableSessions={availableSessions}
                  registrations={registrations}
                  actions={[
                    {
                      name: 'self-register',
                      type: MODAL_BUTTON,
                      label: trans(!isEmpty(activeSession) && isFull(activeSession) ? 'register_waiting_list' : 'self_register', {}, 'actions'),
                      modal: [MODAL_COURSE_REGISTRATION, {
                        course: course,
                        session: activeSession,
                        available: availableSessions,
                        register: (course, sessionId = null, registrationData = null) => {
                          dispatch(actions.register(course, sessionId, registrationData)).then(() => {
                            window.location.reload()
                          })
                        }
                      }],
                      primary: !props.managed && !hasPermission('edit', course),
                      size: 'lg',
                      displayed: selfRegistration
                    }, {
                      name: 'open',
                      size: 'lg',
                      type: CALLBACK_BUTTON,
                      label: trans('open-training', {}, 'actions'),
                      callback: props.dismiss,
                      displayed: props.managed || hasPermission('edit', course),
                      primary: true
                    }
                  ]}
                />
              )
            }
          }
        ]}
      />
    </Router>
  )
}

RestrictionMain.propTypes = {
  workspace: T.object.isRequired,
  managed: T.bool.isRequired,
  errors: T.shape({
    trainings: T.shape({
      course: T.object,
      defaultSession: T.object,
      availableSessions: T.array,
      registrations: T.object
    })
  }),
  dismiss: T.func.isRequired,
  path: T.string.isRequired
}

export {
  RestrictionMain
}
