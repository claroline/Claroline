import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {Routes} from '#/main/app/router/components/routes'
import {Route as RouteTypes} from '#/main/app/router/prop-types'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {LINK_BUTTON} from '#/main/app/buttons'

import {route as toolRoute} from '#/main/core/tool/routing'
import {route as workspaceRoute} from '#/main/core/workspace/routing'

import {
  ResourceNode as ResourceNodeTypes,
  UserEvaluation as UserEvaluationTypes
} from '#/main/core/resource/prop-types'
import {getActions, getToolbar} from '#/main/core/resource/utils'
import {ToolPage} from '#/main/core/tool/containers/page'
import {constants as toolConst} from '#/main/core/tool/constants'
import {ResourceIcon} from '#/main/core/resource/components/icon'
import {ResourceRestrictions} from '#/main/core/resource/components/restrictions'
import {UserProgression} from '#/main/core/resource/components/user-progression'

// FIXME
import {DashboardMain} from '#/plugin/analytics/resource/dashboard/containers/main'

const ResourcePage = (props) => {
  // remove workspace root from path (it's already known by the breadcrumb)
  // find a better way to handle this
  let ancestors
  if (toolConst.TOOL_WORKSPACE === props.contextType) {
    ancestors = props.resourceNode.path.slice(1)
  } else {
    ancestors = props.resourceNode.path.slice(0)
  }

  const routes = [
    {
      path: '/dashboard',
      component: DashboardMain
    }
  ].concat(props.routes)

  return (
    <ToolPage
      id={`resource-${props.resourceNode.id}`}
      className={classes('resource-page', `${props.resourceNode.meta.type}-page`)}
      meta={{
        title: props.resourceNode.name,
        description: props.resourceNode.meta ? props.resourceNode.meta.description : null
      }}
      embedded={props.embedded}
      showHeader={props.embedded ? props.showHeader : true}
      showTitle={get(props.resourceNode, 'display.showTitle')}
      fullscreen={!props.embedded && get(props.resourceNode, 'display.fullscreen')}
      title={props.resourceNode.name}
      subtitle={props.subtitle}
      path={[].concat(ancestors.map(ancestorNode => ({
        type: LINK_BUTTON,
        label: ancestorNode.name,
        target: `${props.basePath}/${ancestorNode.slug}`
      })), props.path)}
      poster={props.resourceNode.poster ? props.resourceNode.poster.url : undefined}
      icon={get(props.resourceNode, 'display.showIcon') && (props.userEvaluation ?
        <UserProgression
          userEvaluation={props.userEvaluation}
          width={70}
          height={70}
        /> :
        <ResourceIcon
          mimeType={props.resourceNode.meta.mimeType}
        />
      )}
      primaryAction={getToolbar(props.primaryAction, true)}
      actions={getActions([props.resourceNode], {
        add: () => {
          props.reload()
        },
        update: (resourceNodes) => {
          // checks if the action have modified the current node
          const currentNode = resourceNodes.find(node => node.id === props.resourceNode.id)
          if (currentNode) {
            // grabs updated data
            props.reload()
          }
        },
        delete: (resourceNodes) => {
          // checks if the action have deleted the current node
          const currentNode = resourceNodes.find(node => node.id === props.resourceNode.id)
          if (currentNode) {
            let redirect
            if (toolConst.TOOL_WORKSPACE === props.contextType && currentNode.workspace) {
              redirect = workspaceRoute(currentNode.workspace, 'resources')
            } else {
              redirect = toolRoute('resources')
            }

            if (currentNode.parent) {
              redirect += '/'+currentNode.parent.slug
            }

            props.history.push(redirect)
          }
        }
      }, props.basePath, props.currentUser, false, props.disabledActions).then((actions) => [].concat(props.customActions || [], actions))}
    >
      {!isEmpty(props.accessErrors) &&
        <ResourceRestrictions
          errors={props.accessErrors}
          dismiss={props.dismissRestrictions}
          managed={props.managed}
          authenticated={props.authenticated}
          checkAccessCode={(code) => props.checkAccessCode(props.resourceNode, code, props.embedded)}
        />
      }

      {isEmpty(props.accessErrors) && !isEmpty(routes) &&
        <Routes
          path={`${props.basePath}/${props.resourceNode.slug}`}
          routes={routes}
          redirect={props.redirect}
        />
      }

      {isEmpty(props.accessErrors) &&
        props.children
      }
    </ToolPage>
  )
} 

ResourcePage.propTypes = {
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,

  basePath: T.string,
  contextType: T.string.isRequired,
  currentUser: T.object,
  embedded: T.bool,
  showHeader: T.bool,
  managed: T.bool.isRequired,
  authenticated: T.bool.isRequired,
  subtitle: T.string,
  path: T.arrayOf(T.shape({
    label: T.string.isRequired,
    target: T.string.isRequired
  })),

  /**
   * The current resource node.
   */
  resourceNode: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired,

  accessErrors: T.object,

  reload: T.func.isRequired,
  dismissRestrictions: T.func.isRequired,
  checkAccessCode: T.func,

  /**
   * The current user evaluation.
   */
  userEvaluation: T.shape(
    UserEvaluationTypes.propTypes
  ),

  // the name of the primary action of the resource (if we want to override the default one).
  // it can contain more than one action name
  primaryAction: T.string,

  customActions: T.arrayOf(T.shape(
    ActionTypes.propTypes
  )),

  // resource content
  routes: T.arrayOf(
    T.shape(RouteTypes.propTypes).isRequired
  ),
  redirect: T.arrayOf(T.shape({
    disabled: T.bool,
    from: T.string.isRequired,
    to: T.string.isRequired,
    exact: T.bool
  })),
  children: T.node,
  disabledActions: T.arrayOf(T.string)
}

ResourcePage.defaultProps = {
  path: [],
  routes: [],
  disabledActions: []
}

export {
  ResourcePage
}
