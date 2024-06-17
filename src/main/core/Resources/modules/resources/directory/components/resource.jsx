import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ResourcePage} from '#/main/core/resource/containers/page'

import {PlayerMain} from '#/main/core/resources/directory/player/containers/main'
import {EditorMain} from '#/main/core/resources/directory/editor/containers/main'
import {DirectorySummary} from '#/main/core/resources/directory/containers/summary'

const DirectoryResource = (props) =>
  <ResourcePage
    /*primaryAction="add"*/
    disabledActions={props.storageLock ? ['add', 'add_files', 'copy'] : []}
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
      }, {
        path: '/summary',
        component: DirectorySummary
      }
    ]}
  />

DirectoryResource.propTypes = {
  storageLock: T.bool.isRequired
}

export {
  DirectoryResource
}