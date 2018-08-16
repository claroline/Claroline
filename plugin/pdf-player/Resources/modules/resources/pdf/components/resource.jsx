import React from 'react'

import {RoutedPageContent} from '#/main/core/layout/router'
import {ResourcePage} from '#/main/core/resource/containers/page'

import {Player} from '#/plugin/pdf-player/resources/pdf/player/components/player'

const pdfPlayer = () =>
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
  pdfPlayer
}