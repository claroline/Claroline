import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {Resource} from '#/main/core/resource'

import {DirectoryEditor} from '#/main/core/resources/directory/editor/components/main'
import {DirectoryPlayer} from '#/main/core/resources/directory/containers/player'

const DirectoryResource = (props) =>
  <Resource
    {...omit(props, 'storageLock')}
    disabledActions={props.storageLock ? ['add', 'add_files', 'copy'] : []}
    editor={DirectoryEditor}
    pages={[
      {
        path: '/',
        exact: true,
        component: DirectoryPlayer
      }
    ]}
  />

DirectoryResource.propTypes = {
  storageLock: T.bool.isRequired
}

export {
  DirectoryResource
}
