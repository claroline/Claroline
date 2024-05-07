import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {Resource} from '#/main/core/resource'

import {PathOverview} from '#/plugin/path/resources/path/containers/overview'
import {PathEditor} from '#/plugin/path/resources/path/editor/components/main'
import {PlayerMain} from '#/plugin/path/resources/path/player/containers/main'
import {PathSummary} from '#/plugin/path/resources/path/containers/summary'

const PathResource = props =>
  <Resource
    {...omit(props, 'editable', 'overview')}
    styles={['claroline-distribution-plugin-path-path-resource']}
    overviewPage={PathOverview}
    editor={PathEditor}
    pages={[
      {
        path: '/summary',
        component: PathSummary
      }, {
        path: '/play',
        component: PlayerMain
      }
    ]}
    redirect={[
      // redirect to player when no overview
      {from: '/', exact: true, to: '/play', disabled: props.overview}
    ]}
  />

PathResource.propTypes = {
  path: T.string.isRequired,
  editable: T.bool.isRequired,
  overview: T.bool.isRequired
}

export {
  PathResource
}
