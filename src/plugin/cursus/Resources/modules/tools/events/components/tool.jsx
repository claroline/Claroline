import React from 'react'
import get from 'lodash/get'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {Course} from '#/plugin/cursus/course/containers/main'
import {EventsAll} from '#/plugin/cursus/tools/events/components/all'
import {EventsPublic} from '#/plugin/cursus/tools/events/components/public'
import {EventsDetails} from '#/plugin/cursus/tools/events/containers/details'
import {EventsPresences} from '#/plugin/cursus/tools/events/containers/presences'
import {EventsRegistered} from '#/plugin/cursus/tools/events/components/registered'

const EventsTool = (props) =>
  <Routes
    path={props.path}
    redirect={[
      {from: '/', exact: true, to: '/about'}
    ]}
    routes={[
      {
        path: '/about',
        onEnter: () => props.openCourse(props.course.slug),
        render: (params = {}) => (
          <Course
            path={props.path+'/about'}
            slug={props.course.slug}
            history={params.history}
          />
        )
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
        render: () => (
          <EventsAll
            path={props.path}
            contextId={get(props.currentContext, 'data.id')}
          />
        ),
        disabled: !props.canEdit && !props.canRegister
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
  openCourse: T.func,
  course: T.shape({
    slug: T.string
  })
}

export {
  EventsTool
}
