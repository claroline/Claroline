import React from 'react'

import {LINK_BUTTON} from '#/main/app/buttons'

import {trans} from '#/main/core/translation'
import {RoutedPageContent} from '#/main/core/layout/router'
import {ResourcePage} from '#/main/core/resource/containers/page'

import {Player} from '#/plugin/web-resource/resources/web-resource/player/components/player'
import {Editor} from '#/plugin/web-resource/resources/web-resource/editor/components/editor'

const WebResource = () =>
  <ResourcePage
    customActions={[
      {
        type: LINK_BUTTON,
        icon: 'fa fa-home',
        label: trans('show_overview'),
        target: '/',
        exact: true
      }
    ]}
  >
    <RoutedPageContent
      headerSpacer={true}
      routes={[
        {
          path: '/',
          exact: true,
          component: Player
        }, {
          path: '/edit',
          component: Editor
        }
      ]}
    />
  </ResourcePage>


export {
  WebResource
}
