import React from 'react'


import {Routes} from '#/main/app/router'

import {Flagged} from '#/plugin/forum/resources/forum/moderation/components/flagged'
import {BlockedMessages} from '#/plugin/forum/resources/forum/moderation/components/blocked-messages'

const Moderation = () =>
  <Routes
    routes={[
      {
        path: '/moderation/flagged',
        component: Flagged
      }, {
        path: '/moderation/blocked',
        component: BlockedMessages
      }
    ]}
  />



export {
  Moderation
}
