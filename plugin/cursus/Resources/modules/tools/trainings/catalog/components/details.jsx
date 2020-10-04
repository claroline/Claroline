import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {Routes} from '#/main/app/router/components/routes'
import {trans} from '#/main/app/intl/translation'

import {LINK_BUTTON} from '#/main/app/buttons'

import {route} from '#/plugin/cursus/routing'
import {Course as CourseTypes} from '#/plugin/cursus/prop-types'
import {CoursePage} from '#/plugin/cursus/course/components/page'
import {CourseDetails} from '#/plugin/cursus/course/components/details'

const CatalogDetails = (props) =>
  <CoursePage
    path={props.course ? [
      {
        type: LINK_BUTTON,
        label: trans('catalog', {}, 'cursus'),
        target: `${props.path}/catalog`
      }, {
        type: LINK_BUTTON,
        label: props.course.name,
        target: route(props.course)
      }
    ] : undefined}
    currentContext={props.currentContext}
    course={props.course}
    activeSession={props.activeSession}
  >
    {props.course &&
      <Routes
        path={props.path+'/catalog'+'/'+props.course.slug}
        redirect={[
          {from: '/', exact: true, to: '/'+get(props.activeSession, 'id'), disabled: !props.activeSession}
        ]}
        routes={[
          {
            path: '/',
            disabled: !!props.activeSession,
            render() {
              const CurrentCourse = (
                <CourseDetails
                  path={props.path+'/catalog'}
                  course={props.course}
                  availableSessions={props.availableSessions}
                  register={props.register}
                />
              )

              return CurrentCourse
            }
          }, {
            path: '/:id',
            onEnter(params = {}) {
              if (params.id) {
                props.openSession(params.id)
              }
            },
            render() {
              const CurrentCourse = (
                <CourseDetails
                  path={props.path+'/catalog'}
                  course={props.course}
                  activeSession={props.activeSession}
                  activeSessionRegistration={props.activeSessionRegistration}
                  availableSessions={props.availableSessions}
                  register={props.register}
                />
              )

              return CurrentCourse
            }
          }
        ]}
      />
    }
  </CoursePage>

CatalogDetails.propTypes = {
  path: T.string.isRequired,
  currentContext: T.shape({
    type: T.oneOf(['administration', 'desktop', 'workspace']),
    data: T.object
  }).isRequired,
  course: T.shape(
    CourseTypes.propTypes
  ),
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
  CatalogDetails
}