import React from 'react'
import {PropTypes as T} from 'prop-types'

import {LINK_BUTTON} from '#/main/app/buttons'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {ResourcePage} from '#/main/core/resource/containers/page'

import {Player} from '#/plugin/web-resource/resources/web-resource/player/components/player'
import {Editor} from '#/plugin/web-resource/resources/web-resource/editor/components/editor'

const WebResource = (props) =>
  <ResourcePage
    customActions={[
      {
        type: LINK_BUTTON,
        icon: 'fa fa-home',
        label: trans('show_overview'),
        target: props.path,
        exact: true
      }
    ]}
  >
    <Routes
      path={props.path}
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

WebResource.propTypes = {
  path: T.string.isRequired
}

export {
  WebResource
}
