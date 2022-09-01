import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ResourcePage} from '#/main/core/resource/containers/page'

import {ShortcutPlayer} from '#/plugin/link/resources/shortcut/containers/player'
import {ShortcutEditor} from '#/plugin/link/resources/shortcut/containers/editor'

const ShortcutResource = (props) =>
  <ResourcePage
    routes={[
      {
        path: '/edit',
        component: ShortcutEditor,
        disabled: !props.editable
      }, {
        path: '/',
        component: ShortcutPlayer,
        exact: true
      }
    ]}
  />

ShortcutResource.propTypes = {
  editable: T.bool.isRequired
}

export {
  ShortcutResource
}