import React from 'react'
import get from 'lodash/get'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'
import {Course} from '#/plugin/cursus/course/containers/main'
import {Course as CourseTypes} from '#/plugin/cursus/prop-types'
import {CourseCreation} from '#/plugin/cursus/course/components/creation'
import {CourseEdit} from '#/plugin/cursus/course/components/edit'

import {EmptyCourse} from '#/plugin/cursus/course/components/empty'
import {EventsAll} from '#/plugin/cursus/tools/events/components/all'
import {EventsPublic} from '#/plugin/cursus/tools/events/components/public'
import {EventsDetails} from '#/plugin/cursus/tools/events/containers/details'
import {EventsPresences} from '#/plugin/cursus/tools/events/containers/presences'
import {EventsRegistered} from '#/plugin/cursus/tools/events/components/registered'

const EventsTool = (props) =>
  <Routes
    path={props.path}
    redirect={[
      {from: '/', exact: true, to: props.course ? '/about/' + props.course.slug : '/about'}
    ]}
    routes={[
      {
        path: '/new',
        onEnter: () => props.openForm(null, CourseTypes.defaultProps, props.currentContext.data),
        disabled: !props.canEdit,
        component: CourseCreation
      }, {
        path: '/about/:courseSlug/edit',
        onEnter: () => props.openForm(props.course.slug),
        component: CourseEdit
      }, {
        path: '/about',
        onEnter: () => {
          if (props.course) {
            return props.openCourse(props.course.slug)
          }
        },
        render: (params = {}) => {
          if (props.course) {
            return (
              <Course
                path={props.path + '/about/' + props.course.slug}
                slug={props.course.slug}
                history={params.history}
              />)
          } else {
            return (<EmptyCourse path={props.path} canEdit={props.canEdit}/>)
          }
        }
      }, {
        path: '/registered',
        onEnter: props.invalidateList,
        render: () => (
          <EventsRegistered
            path={props.path}
            contextId={get(props.currentContext, 'data.id')}
            invalidateList={props.invalidateList}
          />
        )
      }, {
        path: '/public',
        onEnter: props.invalidateList,
        render: () => (
          <EventsPublic
            path={props.path}
            contextId={get(props.currentContext, 'data.id')}
          />
        )
      }, {
        path: '/all',
        onEnter: props.invalidateList,
        disabled: !props.canEdit && !props.canRegister,
        render: () => (
          <EventsAll
            path={props.path}
            contextId={get(props.currentContext, 'data.id')}
          />
        )
      }, {
        path: '/presences',
        component: EventsPresences
      }, {
        path: '/:id',
        onEnter: (params = {}) => props.open(params.id),
        component: EventsDetails
      }
    ]}
  />


EventsTool.propTypes = {
  path: T.string.isRequired,
  currentContext: T.shape({
    type: T.string,
    data: T.object
  }).isRequired,
  canEdit: T.bool.isRequired,
  canRegister: T.bool.isRequired,
  invalidateList: T.func.isRequired,
  open: T.func.isRequired,
  openForm: T.func.isRequired,
  openCourse: T.func,
  course: T.shape({
    slug: T.string
  })
}

export {
  EventsTool
}
