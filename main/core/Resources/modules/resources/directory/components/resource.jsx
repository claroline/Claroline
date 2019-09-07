import React from 'react'

import {ResourcePage} from '#/main/core/resource/containers/page'

import {PlayerMain} from '#/main/core/resources/directory/player/containers/main'
import {EditorMain} from '#/main/core/resources/directory/editor/containers/main'

const DirectoryResource = () =>
  <ResourcePage
    primaryAction="add"
    routes={[
      {
        path: '/:all(all)?',
        exact: true,
        render(routeProps) {
          return (
            <PlayerMain
              all={routeProps.match.params.all}
            />
          )
        }
      }, {
        path: '/edit',
        component: EditorMain
      }
    ]}
  />

export {
  DirectoryResource
}