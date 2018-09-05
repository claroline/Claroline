import React from 'react'

import {Routes} from '#/main/app/router'
import {ResourcePage} from '#/main/core/resource/containers/page'

import {DirectoryPlayer} from '#/main/core/resources/directory/player/components/directory'
import {DirectoryEditor} from '#/main/core/resources/directory/editor/components/directory'

const DirectoryResource = () =>
  <ResourcePage
    primaryAction="add"
  >
    <Routes
      routes={[
        {
          path: '/',
          exact: true,
          component: DirectoryPlayer
        }, {
          path: '/edit',
          component: DirectoryEditor
        }
      ]}
    />
  </ResourcePage>

export {
  DirectoryResource
}