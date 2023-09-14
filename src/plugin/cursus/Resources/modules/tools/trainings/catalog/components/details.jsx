import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {Routes} from '#/main/app/router'

import {route} from '#/plugin/cursus/routing'
import {Course as CourseTypes, Session as SessionTypes} from '#/plugin/cursus/prop-types'
import {CoursePage} from '#/plugin/cursus/course/components/page'
import {CourseDetails} from '#/plugin/cursus/course/components/details'

const CatalogDetails = (props) =>
  <CoursePage
    path={props.path}
    course={props.course}
    activeSession={props.activeSession}
  >
    {props.course &&
      <Routes
        path={route(props.course)}
        routes={[
          {
            path: '/:id?',
            onEnter: (params = {}) => {
              if (params.id && !['sessions', 'participants', 'pending', 'events'].includes(params.id)) {
                props.openSession(params.id)
              } else {
                props.openSession(get(props.defaultSession, 'id') || null)
              }
            },
            render: (routerProps) => (
              <CourseDetails
                path={route(props.course)+'/'+(routerProps.match.params.id && !['sessions', 'participants', 'pending', 'events'].includes(routerProps.match.params.id) ? routerProps.match.params.id : '')}
                course={props.course}
                activeSession={props.activeSession}
                availableSessions={props.availableSessions}
                registrations={props.registrations}
                participantsView={props.participantsView}
                switchParticipantsView={props.switchParticipantsView}
                reload={props.reload}
                register={(course, sessionId = null, registrationData = null) => {
                  props.register(course, sessionId, registrationData).then(() => {
                    props.reload(course.slug)

                    if (!isEmpty(sessionId)) {
                      props.history.push(route(course, {id: sessionId}))
                    }
                  })
                }}
              />
            )
          }
        ]}
      />
    }
  </CoursePage>

CatalogDetails.propTypes = {
  path: T.string.isRequired,
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  course: T.shape(
    CourseTypes.propTypes
  ),
  defaultSession: T.shape(
    SessionTypes.propTypes
  ),
  activeSession: T.shape(
    SessionTypes.propTypes
  ),
  availableSessions: T.arrayOf(T.shape(
    SessionTypes.propTypes
  )),
  registrations: T.shape({
    users: T.array.isRequired,
    groups: T.array.isRequired,
    pending: T.array.isRequired
  }),
  participantsView: T.string.isRequired,
  switchParticipantsView: T.func.isRequired,
  openSession: T.func.isRequired,
  reload: T.func.isRequired,
  register: T.func.isRequired
}

export {
  CatalogDetails
}