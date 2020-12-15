import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {Routes} from '#/main/app/router'

import {EventsAll} from '#/plugin/cursus/tools/events/components/all'
import {EventsRegistered} from '#/plugin/cursus/tools/events/components/registered'
import {EventsPublic} from '#/plugin/cursus/tools/events/components/public'
import {EventsPending} from '#/plugin/cursus/tools/events/components/pending'
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
        onEnter: () => props.invalidateList(),
        render: () => (
          <EventsRegistered
            path={props.path}
            contextId={props.contextId}
            invalidateList={props.invalidateList}
          />
        )
      }, {
        path: '/public',
        onEnter: () => props.invalidateList(),
        render: () => (
          <EventsPublic
            path={props.path}
            contextId={get(props.currentContext, 'data.id')}
          />
        )
      }, {
        path: '/all',
        onEnter: () => props.invalidateList(),
        render: () => (
          <EventsAll
            path={props.path}
            contextId={get(props.currentContext, 'data.id')}
          />
        ),
        disabled: !props.canEdit
      }, {
        path: '/pending',
        onEnter: () => props.invalidateList(),
        render: () => (
          <EventsPending
            path={props.path}
            contextId={get(props.currentContext, 'data.id')}
          />
        ),
        disabled: !props.canAdministrate
      }, {
        path: '/:id',
        component: EventsDetails,
        onEnter: (params = {}) => props.open(params.id)
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
  canAdministrate: T.bool.isRequired,
  invalidateList: T.func.isRequired,
  open: T.func.isRequired
}

export {
  EventsTool
}
