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
      path={props.path}
      routes={[
        {
          path: '/',
          component: Player,
          exact: true
        }, {
          path: '/edit',
          render: () => {
            const component = <Editor path={props.path} />

            return component
          },
          onEnter: () => props.resetForm(props.rssFeed),
          disabled: !props.editable
        }
      ]}
    />
  </ResourcePage>

RssFeedResource.propTypes = {
  path: T.string.isRequired,
  rssFeed: T.shape({
    id: T.string,
    url: T.string
  }).isRequired,
  editable: T.bool.isRequired,
  resetForm: T.func.isRequired
}

export {
  RssFeedResource
}
