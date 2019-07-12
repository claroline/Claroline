import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'
import {ResourcePage} from '#/main/core/resource/containers/page'

import {Player} from '#/plugin/url/resources/url/player/components/player'
import {Editor} from '#/plugin/url/resources/url/editor/components/editor'

const UrlResource = (props) =>
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

UrlResource.propTypes = {
  path: T.string.isRequired
}

export {
  UrlResource
}
