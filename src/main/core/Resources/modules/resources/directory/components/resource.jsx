import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {ResourcePage} from '#/main/core/resource/containers/page'

import {PlayerMain} from '#/main/core/resources/directory/player/containers/main'
import {EditorMain} from '#/main/core/resources/directory/editor/containers/main'
import {DirectorySummary} from '#/main/core/resources/directory/containers/summary'
import {Resource} from '#/main/core/resource/'


const DirectoryResource = (props) =>
  <Resource {...omit(props, 'storageLock')}>
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
  </Resource>

DirectoryResource.propTypes = {
  storageLock: T.bool.isRequired
}

export {
  DirectoryResource
}