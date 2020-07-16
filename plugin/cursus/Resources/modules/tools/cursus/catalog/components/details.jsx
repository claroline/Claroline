import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router/components/routes'
import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {LINK_BUTTON} from '#/main/app/buttons'

import {route} from '#/plugin/cursus/routing'
import {Course as CourseTypes} from '#/plugin/cursus/course/prop-types'
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
    primaryAction="edit"
    actions={props.course ? [
      {
        name: 'edit',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-pencil',
        label: trans('edit', {}, 'actions'),
        target: route(props.course) + '/edit',
        displayed: hasPermission('edit', props.course),
        primary: true
      }
    ] : undefined}
    course={props.course}
  >
    {props.course &&
      <Routes
        path={props.path+'/catalog'+'/'+props.course.slug}
        redirect={[
          {from: '/', exact: true, to: '/'+props.activeSession.id, disabled: !props.activeSession.id}
        ]}
        routes={[
          {
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
                  availableSessions={props.availableSessions}
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
  course: T.shape(
    CourseTypes.propTypes
  ),
  activeSession: T.shape({
    id: T.string.isRequired
  }),
  availableSessions: T.arrayOf(T.shape({
    // TODO : propTypes
  })),
  openSession: T.func.isRequired
}

export {
  CatalogDetails
}