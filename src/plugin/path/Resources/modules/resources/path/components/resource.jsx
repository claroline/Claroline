import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {Resource, ResourcePage} from '#/main/core/resource'

import {PathOverview} from '#/plugin/path/resources/path/containers/overview'
import {EditorMain} from '#/plugin/path/resources/path/editor/containers/main'
import {PlayerMain} from '#/plugin/path/resources/path/player/containers/main'
import {PathSummary} from '#/plugin/path/resources/path/containers/summary'

const PathResource = props =>
  <Resource
    {...omit(props, 'editable', 'overview')}
    styles={['claroline-distribution-plugin-path-path-resource']}
  >
    <ResourcePage
      customActions={[
        /*{
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-home',
          label: trans('show_overview'),
          displayed: props.overview,
          target: props.path,
          exact: true
        }, {
          name: "start",
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-play',
          label: trans('start', {}, 'actions'),
          target: `${props.path}/play`
        }*/
      ]}
      routes={[
        {
          path: '/summary',
          component: PathSummary
        }, {
          path: '/edit',
          component: EditorMain,
          disabled: !props.editable
        }, {
          path: '/play',
          component: PlayerMain
        }, {
          path: '/',
          exact: true,
          component: PathOverview,
          disabled: !props.overview
        }
      ]}
      redirect={[
        // redirect to player when no overview
        {from: '/', exact: true, to: '/play', disabled: props.overview}
      ]}
    />
  </Resource>

PathResource.propTypes = {
  path: T.string.isRequired,
  editable: T.bool.isRequired,
  overview: T.bool.isRequired
}

export {
  PathResource
}
