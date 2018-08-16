import React from 'react'

import {RoutedPageContent} from '#/main/core/layout/router'
import {ResourcePage} from '#/main/core/resource/containers/page'

import {Player} from '#/plugin/web-resource/resources/web-resource/player/components/player'

const WebResource = () =>
  <ResourcePage>
    <RoutedPageContent
      headerSpacer={true}
      routes={[
        {
          path: '/',
          exact: true,
          component: Player
        }
      ]}
    />
  </ResourcePage>


export {
  WebResource
}
