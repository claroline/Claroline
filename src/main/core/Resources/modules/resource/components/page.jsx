import React, {useCallback, useContext} from 'react'
import {useDispatch, useSelector} from 'react-redux'
import classes from 'classnames'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {ToolPage} from '#/main/core/tool'

import {ResourceContext} from '#/main/core/resource/context'
import {getActions} from '#/main/core/resource/utils'
import {selectors, actions} from '#/main/core/resource/store'
import {route} from '#/main/core/resource/routing'
import {route as workspaceRoute} from '#/main/core/workspace/routing'

const ResourcePage = (props) => {
  const resourceDef = useContext(ResourceContext)

  const currentUser = useSelector(securitySelectors.currentUser)
  const basePath = useSelector(selectors.basePath)
  const resourcePath = useSelector(selectors.path)
  const resourceNode = useSelector(selectors.resourceNode)
  const embedded = useSelector(selectors.embedded)
  const showHeader = useSelector(selectors.showHeader)
  
  const dispatch = useDispatch()
  const reload = useCallback(() => dispatch(actions.reload()), [get(resourceNode, 'id')])

  // remove workspace root from path (it's already known by the breadcrumb)
  const breadcrumb = []
  if (get(resourceNode, 'parent') && !get(resourceNode, 'parent.root')) {
    breadcrumb.push({
      label: get(resourceNode, 'parent.name'),
      target: `${basePath}/${get(resourceNode, 'parent.slug')}`
    })
  }

  return (
    <ToolPage
      className={classes('resource-page', `${resourceNode.meta.type}-page`, props.className)}
      meta={{
        title: resourceNode.name,
        description: resourceNode.meta ? resourceNode.meta.description : null
      }}
      breadcrumb={breadcrumb.concat([
        {
          label: resourceNode.name,
          target: resourcePath
        }
      ], props.breadcrumb || [])}
      poster={props.poster || get(resourceNode, 'poster')}
      title={props.title || props.subtitle || resourceNode.name}
      embedded={embedded}
      showHeader={!embedded || showHeader}
      menu={{
        nav: resourceDef.menu,
        toolbar: 'configure more',
        // get actions injected through plugins and the ones defined by the current tool
        actions: getActions([resourceNode], {
          add: reload,
          update: (resourceNodes) => {
            // checks if the action have modified the current node
            if (resourceNodes.find(node => node.id === resourceNode.id)) {
              reload()
            }
          },
          delete: (resourceNodes) => {
            // checks if the action have deleted the current node
            const currentNode = resourceNodes.find(node => node.id === resourceNode.id)
            if (currentNode) {
              let redirect
              if (currentNode.parent) {
                redirect = route(currentNode.parent)
              } else {
                redirect = workspaceRoute(currentNode.workspace, 'resources')
              }

              props.history.push(redirect)
            }
          }
        }, basePath, currentUser, false).then(loadedActions => [].concat(loadedActions, resourceDef.actions || []))
      }}

      {...omit(props, 'className', 'poster', 'styles', 'embedded', 'showHeader')}
      styles={[].concat(resourceDef.styles, props.styles || [])}
    >
      {props.children}
    </ToolPage>
  )
} 

ResourcePage.propTypes = ToolPage.propTypes
ResourcePage.defaultProps = ToolPage.defaultProps

export {
  ResourcePage
}
