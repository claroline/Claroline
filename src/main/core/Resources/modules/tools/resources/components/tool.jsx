import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Tool} from '#/main/core/tool'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourcesTrash} from '#/main/core/tools/resources/containers/trash'
import {ResourceWrapper} from '#/main/core/resource/containers/wrapper'
import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'

const ResourcesTool = props =>
  <Tool
    {...props}
    redirect={props.root ? [
      {from: '/', exact: true, to: `/${props.root.slug}`}
    ] : undefined}
    menu={[
      {
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-home',
        label: trans('trash'),
        target: `${props.path}/trash`,
        displayed: props.canAdministrate
      }, {
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-sitemap',
        label: trans('Arborescence'),
        target: `${props.path}/summary`
      }
    ]}
    pages={[
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
