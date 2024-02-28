import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {EventsAll} from '#/plugin/cursus/tools/trainings/event/components/all'
import {EventsRegistered} from '#/plugin/cursus/tools/trainings/event/components/registered'
import {EventsPublic} from '#/plugin/cursus/tools/trainings/event/components/public'
import {EventsDetails} from '#/plugin/cursus/tools/events/containers/details'


const EventMain = (props) =>
  <Routes
    path={props.path+'/events'}
    redirect={[
      {from: '/', exact: true, to: '/registered', disabled: !props.authenticated},
      {from: '/', exact: true, to: '/public', disabled: props.authenticated}
    ]}
    routes={[
      {
        path: '/registered',
        onEnter: props.invalidateList,
        disabled: !props.authenticated,
        render: () => (
          <EventsRegistered
            path={props.path+'/events'}
          />
        )
      }, {
        path: '/public',
        onEnter: props.invalidateList,
        render: () => (
          <EventsPublic
            path={props.path+'/events'}
          />
        )
      }, {
        path: '/all',
        onEnter: props.invalidateList,
        render: () => (
          <EventsAll
            path={props.path+'/events'}
          />
        ),
        disabled: !props.authenticated || !props.canEdit || !props.canRegister
      }, {
        path: '/:id',
        onEnter: (params = {}) => props.open(params.id),
        render: () => (
          <EventsDetails
            path={props.path+'/events'}
          />
        )
      }
    ]}
  />

EventMain.propTypes = {
  path: T.string.isRequired,
  authenticated: T.bool.isRequired,
  canEdit: T.bool.isRequired,
  canRegister: T.bool.isRequired,
  invalidateList: T.func.isRequired,
  open: T.func.isRequired
}

export {
  EventMain
}