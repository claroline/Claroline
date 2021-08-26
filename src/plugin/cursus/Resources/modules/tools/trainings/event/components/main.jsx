import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'

import {EventsAll} from '#/plugin/cursus/tools/events/components/all'
import {EventsRegistered} from '#/plugin/cursus/tools/events/components/registered'
import {EventsPublic} from '#/plugin/cursus/tools/events/components/public'
import {EventsDetails} from '#/plugin/cursus/tools/events/containers/details'

import {selectors} from '#/plugin/cursus/tools/trainings/event/store'

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
            name={selectors.STORE_NAME}
            invalidateList={props.invalidateList}
            definition={[
              {
                name: 'session',
                label: trans('session', {}, 'cursus'),
                type: 'training_session',
                displayed: true
              }
            ]}
          />
        )
      }, {
        path: '/public',
        onEnter: props.invalidateList,
        render: () => (
          <EventsPublic
            path={props.path+'/events'}
            name={selectors.STORE_NAME}
            definition={[
              {
                name: 'session',
                label: trans('session', {}, 'cursus'),
                type: 'training_session',
                displayed: true
              }
            ]}
          />
        )
      }, {
        path: '/all',
        onEnter: props.invalidateList,
        render: () => (
          <EventsAll
            path={props.path+'/events'}
            name={selectors.STORE_NAME}
            definition={[
              {
                name: 'session',
                label: trans('session', {}, 'cursus'),
                type: 'training_session',
                displayed: true
              }
            ]}
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