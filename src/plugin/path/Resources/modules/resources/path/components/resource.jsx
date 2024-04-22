import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {Resource, ResourcePage} from '#/main/core/resource'

import {PathOverview} from '#/plugin/path/resources/path/containers/overview'
import {EditorMain} from '#/plugin/path/resources/path/editor/containers/main'
import {PlayerMain} from '#/plugin/path/resources/path/player/containers/main'
import {PathSummary} from '#/plugin/path/resources/path/containers/summary'
import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'

const PathResource = props =>
  <Resource
    {...omit(props, 'editable', 'overview')}
    styles={['claroline-distribution-plugin-path-path-resource']}
    /*menu={[
      {
        name: 'summary',
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-sitemap',
        label: trans('summary'),
        target: `${props.path}/summary`
      }
    ]}*/
    overview={PathOverview}
    pages={[
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
