import React from 'react'


import {Routes} from '#/main/app/router'

import {Flagged} from '#/plugin/forum/resources/forum/moderation/components/flagged'
import {Blocked} from '#/plugin/forum/resources/forum/moderation/components/blocked'

const Moderation = () =>
  <Routes
    routes={[
      {
        path: '/moderation/flagged',
        component: Flagged
      }, {
        path: '/moderation/blocked',
        component: Blocked
      }
    ]}
  />



export {
  Moderation
}
