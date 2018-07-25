import React from 'react'

import {RoutedPageContent} from '#/main/core/layout/router'
import {ResourcePageContainer} from '#/main/core/resource/containers/page'

import {Player} from '#/plugin/pdf-player/resources/pdf/player/components/player'

const pdfPlayer = () =>
  <ResourcePageContainer>
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
  </ResourcePageContainer>


export {
  pdfPlayer
}