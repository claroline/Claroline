import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Tool} from '#/main/core/tool'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourceWrapper} from '#/main/core/resource/containers/wrapper'
import {ResourcesEditor} from '#/main/core/tools/resources/editor/containers/main'

const ResourcesTool = props =>
  <Tool
    {...props}
    redirect={props.root ? [
      {from: '/', exact: true, to: `/${props.root.slug}`}
    ] : undefined}
    editor={ResourcesEditor}
    pages={[
      {
        path: '/:slug',
        render: (routerProps) => {
          const params = routerProps.match.params

          return <ResourceWrapper slug={params.slug} />
        }
      }
    ]}
  />

ResourcesTool.propTypes = {
  root: T.shape(
    ResourceNodeTypes.propTypes
  )
}

export {
  ResourcesTool
}
