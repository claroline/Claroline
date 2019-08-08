import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {ListParameters as ListParametersTypes} from '#/main/app/content/list/parameters/prop-types'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {CurrentDirectory} from '#/main/core/resource/explorer/components/current'

const ResourceExplorer = props => {
  return (
    <Routes
      path={props.basePath}
      redirect={props.root ? [
        {from: '/', exact: true, to: `/${props.root.meta.slug}`}
      ] : undefined}
      routes={[
        {
          path: props.root ? '/:slug' : '/:slug?',
          onEnter: (params = {}) => props.changeDirectory(params.slug),
          render: () => {
            const Current =
              <CurrentDirectory
                basePath={props.basePath}
                name={props.name}
                primaryAction={props.primaryAction}
                actions={props.actions}
                currentId={props.currentId}
                listConfiguration={props.listConfiguration}
              />

            return Current
          }
        }
      ]}
    />
  )
}

ResourceExplorer.propTypes = {
  basePath: T.string,
  name: T.string.isRequired,
  primaryAction: T.func,
  root: T.shape(
    ResourceNodeTypes.propTypes
  ),
  currentId: T.string,
  listConfiguration: T.shape(
    ListParametersTypes.propTypes
  ).isRequired,
  changeDirectory: T.func.isRequired,
  actions: T.func
}

ResourceExplorer.defaultProps = {
  basePath: ''
}

export {
  ResourceExplorer
}
