import React from 'react'

import {Routes} from '#/main/app/router/components/routes'

import {EventPresence} from '#/plugin/cursus/presence/components/event'
import {SignPresence} from '#/plugin/cursus/presence/components/signing'

const PresenceMain = (props) =>
  <Routes
    path={props.path+'/presence'}
    routes={[
      {
        path: '/:code',
        onEnter: (params) => props.getEventByCode(params.code),
        render: () => (
          <SignPresence path={props.path+'/presence'} />
        )
      }, {
        path: '/',
        onEnter: () => props.resetEvent(),
        render: () => (
          <EventPresence path={props.path+'/presence'} />
        )
      }
    ]}
  />

export {
  PresenceMain
}
