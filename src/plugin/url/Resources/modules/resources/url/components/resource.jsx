import React from 'react'

import {Resource} from '#/main/core/resource'

import {Player} from '#/plugin/url/resources/url/player/containers/player'
import {UrlEditor} from '#/plugin/url/resources/url/components/editor'

const UrlResource = (props) =>
  <Resource
    {...props}
    editor={UrlEditor}
    pages={[
      {
        path: '/',
        component: Player,
        exact: true
      }
    ]}
  />

export {
  UrlResource
}
