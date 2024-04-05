import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

import {Routes} from '#/main/app/router/components/routes'
import {Route as RouteTypes} from '#/main/app/router/prop-types'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Await} from '#/main/app/components/await'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ToolPage} from '#/main/core/tool'
import {ResourceRestrictions} from '#/main/core/resource/containers/restrictions'

import {ResourceEvaluation as ResourceEvaluationTypes} from '#/main/evaluation/resource/prop-types'
import {ResourceIcon} from '#/main/core/resource/components/icon'
import {ResourceMenu} from '#/main/core/resource/containers/menu'
import {getResource} from '#/main/core/resources'

// FIXME
import {EvaluationMain} from '#/main/evaluation/resource/evaluation/containers/main'
import {LogsMain} from '#/main/log/resource/logs/containers/main'

const ResourcePage = (props) => {
  // remove workspace root from path (it's already known by the breadcrumb)
  // remove resource from path (added by ToolPage)
  // find a better way to handle this
  let ancestors  = props.resourceNode.path.slice(1, props.resourceNode.path.length - 1)

  const routes = [
    {
      path: '/evaluation',
      component: EvaluationMain
    }, {
      path: '/logs',
      component: LogsMain
    }
  ].concat(props.routes)

  return (
    <ToolPage
      className={classes('resource-page', `${props.resourceNode.meta.type}-page`, props.className)}
      meta={{
        title: props.resourceNode.name,
        description: props.resourceNode.meta ? props.resourceNode.meta.description : null
      }}
      embedded={props.embedded}
      showHeader={!props.embedded || props.showHeader}
      title={props.title || props.subtitle || props.resourceNode.name}
      breadcrumb={[].concat(ancestors.map(ancestorNode => ({
        label: ancestorNode.name,
        target: `${props.basePath}/${ancestorNode.slug}`
      })), props.breadcrumb || props.path)}
      poster={props.resourceNode.poster}
      icon={get(props.resourceNode, 'display.showIcon') ?
        <ResourceIcon
          mimeType={props.resourceNode.meta.mimeType}
        /> : undefined
      }

      menu={
        <Await
          for={getResource(props.type)}
          then={(module) => {
            if (module.default.menu) {
              return createElement(module.default.menu, {path: `${props.basePath}/${props.resourceNode.slug}`})
            }

            return createElement(ResourceMenu, {path: `${props.basePath}/${props.resourceNode.slug}`})
          }}
        />
      }

      {...omit(props, 'name', 'className', 'path', 'basePath', 'resourceNode', 'poster', 'accessErrors', 'userEvaluation', 'showHeader')}
      actions={props.customActions || props.actions}
    >
      {!isEmpty(props.accessErrors) &&
        <ResourceRestrictions />
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
  className: T.string,
  basePath: T.string,
  embedded: T.bool,
  showHeader: T.bool,
  type: T.string.isRequired,
  /**
   * @deprecated
   */
  subtitle: T.string,
  title: T.string,
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

  /**
   * The current user evaluation.
   */
  userEvaluation: T.shape(
    ResourceEvaluationTypes.propTypes
  ),

  // the name of the primary action of the resource (if we want to override the default one).
  // it can contain more than one action name
  primaryAction: T.string,

  actions: T.arrayOf(T.shape(
    ActionTypes.propTypes
  )),

  /**
   * @deprecated use actions
   */
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
  /*disabledActions: T.arrayOf(T.string)*/
}

ResourcePage.defaultProps = {
  path: [],
  routes: [],
  disabledActions: []
}

export {
  ResourcePage
}
