import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

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
        redirect={[
          {from: '/', exact: true, to: '/'+get(props.defaultSession, 'id'), disabled: !props.defaultSession}
        ]}
        routes={[
          {
            path: '/:id?',
            onEnter: (params = {}) => props.openSession(params.id && !['sessions', 'participants', 'pending', 'events'].includes(params.id) ? params.id : null),
            render: (routerProps) => (
              <CourseDetails
                path={props.path+'/catalog/'+props.course.slug+'/'+(routerProps.match.params.id && !['sessions', 'participants', 'pending', 'events'].includes(routerProps.match.params.id) ? routerProps.match.params.id : '')}
                course={props.course}
                activeSession={props.activeSession}
                activeSessionRegistration={props.activeSessionRegistration}
                availableSessions={props.availableSessions}
                courseRegistration={props.courseRegistration}
                participantsView={props.participantsView}
                switchParticipantsView={props.switchParticipantsView}
              />
            )
          }
        ]}
      />
    }
  </CoursePage>

CatalogDetails.propTypes = {
  path: T.string.isRequired,
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
  activeSessionRegistration: T.shape({
    // TODO : propTypes
  }),
  courseRegistration: T.shape({}),
  participantsView: T.string.isRequired,
  switchParticipantsView: T.func.isRequired,
  openSession: T.func.isRequired
}

export {
  CatalogDetails
}