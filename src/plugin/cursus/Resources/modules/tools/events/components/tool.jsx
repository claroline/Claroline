import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {Routes} from '#/main/app/router'

import {EventsAll} from '#/plugin/cursus/tools/events/components/all'
import {EventsRegistered} from '#/plugin/cursus/tools/events/components/registered'
import {EventsPublic} from '#/plugin/cursus/tools/events/components/public'
import {EventsPresences} from '#/plugin/cursus/tools/events/containers/presences'
import {EventsDetails} from '#/plugin/cursus/tools/events/containers/details'


const EventsTool = (props) =>
  <Routes
    path={props.path}
    redirect={[
      {from: '/', exact: true, to: '/registered'}
    ]}
    routes={[
      {
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
  open: T.func.isRequired
}

export {
  EventsTool
}
