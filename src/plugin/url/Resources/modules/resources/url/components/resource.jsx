import React from 'react'

import {Resource} from '#/main/core/resource'

import {Player} from '#/plugin/url/resources/url/player/containers/player'
import {Editor} from '#/plugin/url/resources/url/editor/containers/editor'

const UrlResource = (props) =>
  <Resource
    {...props}
    pages={[
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
