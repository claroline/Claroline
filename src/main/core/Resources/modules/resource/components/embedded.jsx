import React, {useCallback, useEffect, useRef} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {mount, unmount} from '#/main/app/dom/mount'
import {Routes} from '#/main/app/router'

import {selectors as configSelectors} from '#/main/app/config/store'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {reducer as contextReducer, selectors as contextSelectors} from '#/main/app/context/store'
import {reducer as toolReducer, selectors as toolSelectors} from '#/main/core/tool/store'

import {route as workspaceRoute} from '#/main/core/workspace/routing'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourceWrapper} from '#/main/core/resource/containers/wrapper'
import {route} from '#/main/core/resource/routing'

const ResourceEmbedded = (props) => {
  const containerRef = useRef(null)
  const rootRef = useRef(null)

  const Resource = useCallback(() => (
    <Routes
      redirect={[
        {from: '/', exact: true, to: route(props.resourceNode)}
      ]}
      routes={[
        {
          path: workspaceRoute(get(props.resourceNode, 'workspace'), 'resources')+'/:slug',
          render: (routerProps) => {
            return <ResourceWrapper slug={get(routerProps, 'match.params.slug')} embedded={true} />
          }
        }
      ]}
    />
  ), [get(props.resourceNode, 'id')])

  Resource.displayName = `EmbeddedResource(${props.resourceNode.meta.type})`

  useEffect(() => {
    if (!containerRef.current || !get(props.resourceNode, 'id')) {
      return
    }

    const renderTimeout = setTimeout(() => {
      rootRef.current = mount(containerRef.current, Resource, {
        [contextSelectors.STORE_NAME]: contextReducer,
        [toolSelectors.STORE_NAME]: toolReducer
      }, {
        [securitySelectors.STORE_NAME]: props.security,
        [configSelectors.STORE_NAME]: props.config,
        // mount the resource tool in the store
        context: {
          loaded: true,
          type: 'workspace',
          id: get(props.resourceNode, 'workspace.slug'),
          data: get(props.resourceNode, 'workspace')
        },
        tool: {
          loaded: true,
          name: 'resources'
        },
        resources: {
          root: props.resourceNode
        },
        // mount the resource in the store
        resource: {
          embedded: true,
          showHeader: props.showHeader,
          lifecycle: props.lifecycle
        }
      }, true)
    }, 0)

    return () => {
      if (renderTimeout) {
        clearTimeout(renderTimeout)
      }

      const root = rootRef.current
      if (root) {
        rootRef.current = undefined
        setTimeout(() => {
          unmount(root)
        }, 0)
      }
    }
  }, [get(props.resourceNode, 'id')])

  return (
    <div
      ref={containerRef}
      className={classes('resource-container embedded-resource flex-fill d-flex flex-column', props.className)}
    />
  )
}

ResourceEmbedded.propTypes = {
  className: T.string,
  showHeader: T.bool,
  showActions: T.bool,
  resourceNode: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired,
  // some redux actions to dispatch during the resource lifecycle
  lifecycle: T.shape({
    open: T.func,
    play: T.func,
    end: T.func,
    close: T.func
  }),

  // from store (to build the embedded store)
  security: T.object,
  config: T.object
}

ResourceEmbedded.defaultProps = {
  lifecycle: {}
}

export {
  ResourceEmbedded
}
