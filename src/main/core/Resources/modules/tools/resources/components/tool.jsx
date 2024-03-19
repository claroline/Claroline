import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourceMain} from '#/main/core/resource/containers/main'
import {ResourcesTrash} from '#/main/core/tools/resources/containers/trash'

const ResourcesTool = props =>
  <Routes
    path={props.path}
    redirect={props.root ? [
      {from: '/', exact: true, to: `/${props.root.slug}`}
    ] : undefined}
    routes={[
      {
        path: '/trash',
        exact: true,
        disabled: !props.canAdministrate,
        component: ResourcesTrash
      }, {
        path: '/:slug',
        onEnter: (params = {}) => props.openResource(params.slug),
        component: ResourceMain
      }
    ]}
  />

ResourcesTool.propTypes = {
  path: T.string.isRequired,
  canAdministrate: T.bool.isRequired,
  root: T.shape(
    ResourceNodeTypes.propTypes
  ),
  openResource: T.func.isRequired
}

export {
  ResourcesTool
}
