import React from 'react'

import {ResourcePage} from '#/main/core/resource/containers/page'

import {Player} from '#/main/core/resources/text/player/components/player'
import {Editor} from '#/main/core/resources/text/editor/components/editor'

const TextResource = () =>
  <ResourcePage
    routes={[
      {
        path: '/',
        component: Player,
        exact: true
      }, {
        path: '/edit',
        component: Editor
      }
    ]}
  />

export {
  TextResource
}
