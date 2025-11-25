import React, {useCallback, useContext} from 'react'
import {useDispatch, useSelector} from 'react-redux'
import {useHistory} from 'react-router-dom'
import get from 'lodash/get'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {PageContext} from '#/main/app/page/context'
import {ToolPage} from '#/main/core/tool'
import {route as workspaceRoute} from '#/main/core/workspace/routing'

import {getActions} from '#/main/core/resource/utils'
import {selectors, actions} from '#/main/core/resource/store'
import {route} from '#/main/core/resource/routing'
import {EvaluationShortcut} from '#/main/evaluation/components/shortcut'
import {MODAL_USER_PROGRESSION} from '#/main/evaluation/resource/modals/user-progression'

const ResourcePage = (props) => {
  const resourceDef = useContext(PageContext)

  const currentUser = useSelector(securitySelectors.currentUser)
  const basePath = useSelector(selectors.basePath)
  const resourcePath = useSelector(selectors.path)
  const resourceNode = useSelector(selectors.resourceNode)
  const embedded = useSelector(selectors.embedded)
  const showHeader = useSelector(selectors.showHeader)
  const hasEvaluation = useSelector(selectors.hasEvaluation)
  const userEvaluation = useSelector(selectors.resourceEvaluation)

  const dispatch = useDispatch()
  const history = useHistory()
  const reload = useCallback(() => dispatch(actions.reload()), [get(resourceNode, 'id')])

  return (
    <ToolPage
      className={props.className}
      breadcrumb={props.breadcrumb || [
        {
          label: resourceNode.name,
          target: resourcePath
        }
      ]}
      title={props.title ?
        props.title + ' | ' + resourceNode.name :
        resourceNode.name
      }
      description={props.description || get(resourceNode, 'meta.description')}
      embedded={embedded}
      showHeader={showHeader}
      menu={{
        children: hasEvaluation && userEvaluation && (
          <EvaluationShortcut
            className="my-auto"
            evaluation={merge({}, userEvaluation, {user: currentUser, resourceNode: resourceNode})}
            modal={MODAL_USER_PROGRESSION}
          />
        ),
        nav: resourceDef.menu,
        toolbar: 'edit show-dashboard configure more',
        // get actions injected through plugins and the ones defined by the current resource type
        actions: getActions([resourceNode], {
          add: reload,
          update: (resourceNodes) => {
            // checks if the action has modified the current node
            if (resourceNodes.find(node => node.id === resourceNode.id)) {
              reload()
            }
          },
          delete: (resourceNodes) => {
            // checks if the action has deleted the current node
            const currentNode = resourceNodes.find(node => node.id === resourceNode.id)
            if (currentNode) {
              let redirect
              if (currentNode.parent) {
                redirect = route(currentNode.parent)
              } else {
                redirect = workspaceRoute(currentNode.workspace, 'resources')
              }

              history.push(redirect)
            }
          }
        }, basePath, currentUser, false).then(loadedActions => [].concat(loadedActions, resourceDef.actions || []))
      }}

      {...omit(props, 'className', 'breadcrumb', 'title', 'description')}
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
