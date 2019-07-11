import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'
import {ResourcePage} from '#/main/core/resource/containers/page'

import {Player} from '#/main/core/resources/text/player/components/player'
import {Editor} from '#/main/core/resources/text/editor/components/editor'

const TextResource = (props) =>
  <ResourcePage>
    <Routes
      path={props.path}
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
  </ResourcePage>

TextResource.propTypes = {
  path: T.string.isRequired
}

export {
  TextResource
}
