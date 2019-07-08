import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourceMain} from '#/main/core/resource/containers/main'
import {ResourcesRoot} from '#/main/core/tools/resources/components/root'

const ResourcesTool = props =>
  <Routes
    path={props.path}
    redirect={props.root ? [
      {from: '/', exact: true, to: `/${props.root.id}`}
    ] : undefined}
    routes={[
      {
        path: '/',
        exact: true,
        disabled: !!props.root,
        render: () => {
          return (
            <ResourcesRoot
              path={props.path}
              currentUser={props.currentUser}
              listName={props.listRootName}
              invalidate={props.invalidateRoot}
            />
          )
        }
      }, {
        path: '/:id',
        render: (routeProps) => {
          return (
            <ResourceMain
              path={props.path}
              resourceId={routeProps.match.params.id}
            />
          )
        }
      }
    ]}
  />

ResourcesTool.propTypes = {
  path: T.string.isRequired,
  currentUser: T.object,
  root: T.shape(
    ResourceNodeTypes.propTypes
  ),
  listRootName: T.string.isRequired,
  invalidateRoot: T.func.isRequired
}

export {
  ResourcesTool
}
