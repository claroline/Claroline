import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourcesTrash} from '#/main/core/tools/resources/containers/trash'
import {ResourceWrapper} from '#/main/core/resource/containers/wrapper'
import {Tool} from '#/main/core/tool'

const ResourcesTool = props =>
  <Tool {...props}>
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
          render: (routerProps) => {
            const params = routerProps.match.params

            return <ResourceWrapper slug={params.slug} />
          }
        }
      ]}
    />
  </Tool>

ResourcesTool.propTypes = {
  path: T.string.isRequired,
  canAdministrate: T.bool.isRequired,
  root: T.shape(
    ResourceNodeTypes.propTypes
  )
}

export {
  ResourcesTool
}
