import React from 'react'

import {ResourcePage} from '#/main/core/resource/containers/page'

import {Player} from '#/plugin/url/resources/url/player/containers/player'
import {Editor} from '#/plugin/url/resources/url/editor/containers/editor'

const UrlResource = () =>
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
  UrlResource
}
