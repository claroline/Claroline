import React from 'react'

import {Resource, ResourcePage} from '#/main/core/resource'

import {Player} from '#/plugin/web-resource/resources/web-resource/player/components/player'
import {Editor} from '#/plugin/web-resource/resources/web-resource/editor/components/editor'

const WebResource = (props) =>
  <Resource {...props}>
    <ResourcePage
      routes={[
        {
          path: '/',
          exact: true,
          component: Player
        }, {
          path: '/edit',
          component: Editor
        }
      ]}
    />
  </Resource>

export {
  WebResource
}
