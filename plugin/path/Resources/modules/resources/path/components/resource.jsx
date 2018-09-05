import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {Routes} from '#/main/app/router'
import {ResourcePage} from '#/main/core/resource/containers/page'
import {LINK_BUTTON} from '#/main/app/buttons'

import {Overview} from '#/plugin/path/resources/path/overview/components/overview'
import {Editor} from '#/plugin/path/resources/path/editor/components/editor'
import {Player} from '#/plugin/path/resources/path/player/components/player'

const PathResource = props =>
  <ResourcePage
    styles={['claroline-distribution-plugin-path-path-resource']}
    customActions={[
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-home',
        label: trans('show_overview'),
        displayed: props.overview,
        target: '/',
        exact: true
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-play',
        label: trans('start', {}, 'actions'),
        target: '/play'
      }
    ]}
  >
    <Routes
      routes={[
        {
          path: '/edit',
          component: Editor,
          disabled: !props.editable
        }, {
          path: '/play',
          component: Player
        }, {
          path: '/',
          exact: true,
          component: Overview,
          disabled: !props.overview
        }
      ]}
      redirect={[
        { // redirect to player when no overview
          disabled: props.overview,
          from: '/',
          to: '/play',
          exact: true
        }
      ]}
    />
  </ResourcePage>

PathResource.propTypes = {
  editable: T.bool.isRequired,
  overview: T.bool.isRequired
}

export {
  PathResource
}
