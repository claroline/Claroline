import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ResourcePage} from '#/main/core/resource/containers/page'

import {Player} from '#/main/core/resources/text/player/components/player'
import {Editor} from '#/main/core/resources/text/editor/components/editor'

const TextResource = (props) =>
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

TextResource.propTypes = {
  path: T.string.isRequired
}

export {
  TextResource
}
