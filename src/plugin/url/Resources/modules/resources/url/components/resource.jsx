import React from 'react'

import {ResourcePage} from '#/main/core/resource/containers/page'

import {Player} from '#/plugin/url/resources/url/player/containers/player'
import {Editor} from '#/plugin/url/resources/url/editor/containers/editor'
import {Resource} from '#/main/core/resource'

const UrlResource = (props) =>
  <Resource {...props}>
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
  </Resource>

export {
  UrlResource
}
