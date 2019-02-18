import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'
import {ResourcePage} from '#/main/core/resource/containers/page'

import {Editor} from '#/plugin/rss/resources/rss-feed/editor/components/editor'
import {Player} from '#/plugin/rss/resources/rss-feed/player/components/player'

const RssFeedResource = props =>
  <ResourcePage
    styles={[]}
    customActions={[]}
  >
    <Routes
      routes={[
        {
          path: '/',
          component: Player,
          exact: true
        }, {
          path: '/edit',
          component: Editor,
          disabled: !props.editable
        }
      ]}
    />
  </ResourcePage>

RssFeedResource.propTypes = {
  editable: T.bool.isRequired
}

export {
  RssFeedResource
}
