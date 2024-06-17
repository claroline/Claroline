import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourceMain} from '#/main/core/resource/containers/main'

const ResourcesTool = props =>
  <Routes
    path={props.path}
    redirect={props.root ? [
      {from: '/', exact: true, to: `/${props.root.slug}`}
    ] : undefined}
    routes={[
      /*{
        path: '/',
        exact: true,
        disabled: !!props.root,
        component: ResourcesRoot
      },*/ {
        path: '/:slug',
        onEnter: (params = {}) => props.openResource(params.slug),
        component: ResourceMain
      }
    ]}
  />

ResourcesTool.propTypes = {
  path: T.string.isRequired,
  root: T.shape(
    ResourceNodeTypes.propTypes
  ),
  openResource: T.func.isRequired
}

export {
  ResourcesTool
}
