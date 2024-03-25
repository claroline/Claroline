import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {Resource, ResourcePage} from '#/main/core/resource'

import {ShortcutPlayer} from '#/plugin/link/resources/shortcut/containers/player'
import {ShortcutEditor} from '#/plugin/link/resources/shortcut/containers/editor'

const ShortcutResource = (props) =>
  <Resource {...omit(props, 'editable')}>
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
  </Resource>

ShortcutResource.propTypes = {
  editable: T.bool.isRequired
}

export {
  ShortcutResource
}
