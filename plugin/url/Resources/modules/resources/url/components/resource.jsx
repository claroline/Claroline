import React from 'react'

import {RoutedPageContent} from '#/main/core/layout/router'
import {ResourcePage} from '#/main/core/resource/containers/page'

import {Player} from '#/plugin/url/resources/url/player/components/player'
import {Editor} from '#/plugin/url/resources/url/editor/components/editor'

const UrlResource = () =>
  <ResourcePage>
    <RoutedPageContent
      headerSpacer={true}
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

export {
  UrlResource
}
