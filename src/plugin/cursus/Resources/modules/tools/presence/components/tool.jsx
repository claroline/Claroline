import React from 'react'

import {Tool} from '#/main/core/tool'
import {EventPresence} from '#/plugin/cursus/presence/components/event'
import {SignPresence} from '#/plugin/cursus/presence/components/signing'

const PresenceTool = (props) =>
  <Tool
    {...props}
    pages={[
      {
        path: '/:code',
        render: (routerProps) => (
          <SignPresence code={routerProps.match.params.code} path={props.path} />
        )
      }, {
        path: '/',
        render: () => (
          <EventPresence path={props.path}/>
        )
      }
    ]}
  />

export {
  PresenceTool
}
