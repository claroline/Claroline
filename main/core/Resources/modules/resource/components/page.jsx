import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router/components/routes'
import {Route as RouteTypes} from '#/main/app/router/prop-types'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentLoader} from '#/main/app/content/components/loader'

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
import {ServerErrors} from '#/main/core/resource/components/errors'
import {UserProgression} from '#/main/core/resource/components/user-progression'

// FIXME
import {DashboardMain} from '#/plugin/analytics/resource/dashboard/containers/main'

class ResourcePage extends Component {
  constructor(props) {
    super(props)

    // open resource in fullscreen if configured
    this.state = {
      fullscreen: !this.props.embedded && get(this.props.resourceNode, 'display.fullscreen')
    }
  }

  componentDidMount() {
    this.props.loadResource(this.props.resourceNode, this.props.embedded)
  }

  componentDidUpdate(prevProps) {
    // the resource has changed
    if (this.props.resourceNode.id !== prevProps.resourceNode.id) {
      // load the new one
      this.props.loadResource(this.props.resourceNode, this.props.embedded)
    }
  }

  toggleFullscreen() {
    this.setState({fullscreen: !this.state.fullscreen})
  }

  render() {
    if (!this.props.loaded) {
      return (
        <ContentLoader
          size="lg"
          description="Nous chargeons le contenu de votre ressource"
        />
      )
    }

    // remove workspace root from path (it's already known by the breadcrumb)
    // find a better way to handle this
    let ancestors
    if (toolConst.TOOL_WORKSPACE === this.props.contextType) {
      ancestors = this.props.resourceNode.path.slice(1)
    } else {
      ancestors = this.props.resourceNode.path.slice(0)
    }

    const routes = [
      {
        path: '/dashboard',
        component: DashboardMain
      }
    ].concat(this.props.routes)

    return (
      <ToolPage
        className={classes('resource-page', `${this.props.resourceNode.meta.type}-page`)}
        styles={this.props.styles}
        embedded={this.props.embedded}
        showHeader={this.props.embedded ? this.props.showHeader : true}
        fullscreen={this.state.fullscreen}
        title={this.props.resourceNode.name}
        subtitle={this.props.subtitle}
        path={[].concat(ancestors.map(ancestorNode => ({
          type: LINK_BUTTON,
          label: ancestorNode.name,
          target: `${this.props.basePath}/${ancestorNode.slug}`
        })), this.props.path)}
        poster={this.props.resourceNode.poster ? this.props.resourceNode.poster.url : undefined}
        icon={get(this.props.resourceNode, 'display.showIcon') && (this.props.userEvaluation ?
          <UserProgression
            userEvaluation={this.props.userEvaluation}
            width={70}
            height={70}
          /> :
          <ResourceIcon
            mimeType={this.props.resourceNode.meta.mimeType}
          />
        )}
        toolbar={getToolbar(this.props.primaryAction, true)}
        actions={getActions([this.props.resourceNode], {
          add: () => {
            this.props.loadResource(this.props.resourceNode, this.props.embedded)
          },
          update: (resourceNodes) => {
            // checks if the action have modified the current node
            const currentNode = resourceNodes.find(node => node.id === this.props.resourceNode.id)
            if (currentNode) {
              // grabs updated data
              this.props.updateNode(currentNode)
              this.props.loadResource(this.props.resourceNode, this.props.embedded)
            }
          },
          delete: (resourceNodes) => {
            // checks if the action have deleted the current node
            const currentNode = resourceNodes.find(node => node.id === this.props.resourceNode.id)
            if (currentNode) {
              let redirect
              if (toolConst.TOOL_WORKSPACE === this.props.contextType && currentNode.workspace) {
                redirect = workspaceRoute(currentNode.workspace, 'resources')
              } else {
                redirect = toolRoute('resources')
              }

              if (currentNode.parent) {
                redirect += '/'+currentNode.parent.id
              }

              this.props.history.push(redirect)
            }
          }
        }, this.props.basePath, this.props.currentUser).then((actions) => [].concat(this.props.customActions || [], actions, [
          {
            name: 'fullscreen',
            type: 'callback',
            icon: classes('fa fa-fw', {
              'fa-expand': !this.state.fullscreen,
              'fa-compress': this.state.fullscreen
            }),
            label: trans(this.state.fullscreen ? 'fullscreen_off' : 'fullscreen_on'),
            callback: this.toggleFullscreen.bind(this)
          }
        ]))}
      >
        {!isEmpty(this.props.accessErrors) &&
          <ResourceRestrictions
            errors={this.props.accessErrors}
            dismiss={this.props.dismissRestrictions}
            managed={this.props.managed}
            checkAccessCode={(code) => this.props.checkAccessCode(this.props.resourceNode, code, this.props.embedded)}
          />
        }

        {!isEmpty(this.props.serverErrors) &&
          <ServerErrors errors={this.props.serverErrors}/>
        }

        {isEmpty(this.props.accessErrors) && isEmpty(this.props.serverErrors) && !isEmpty(routes) &&
          <Routes
            path={`${this.props.basePath}/${this.props.resourceNode.slug}`}
            routes={routes}
            redirect={this.props.redirect}
          />
        }

        {isEmpty(this.props.accessErrors) && isEmpty(this.props.serverErrors) &&
          this.props.children
        }
      </ToolPage>
    )
  }
}

ResourcePage.propTypes = {
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,

  basePath: T.string,
  contextType: T.string.isRequired,
  currentUser: T.object,
  loaded: T.bool.isRequired,
  embedded: T.bool,
  showHeader: T.bool,
  managed: T.bool.isRequired,
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
  serverErrors: T.array,

  updateNode: T.func.isRequired,
  loadResource: T.func.isRequired,
  dismissRestrictions: T.func.isRequired,
  checkAccessCode: T.func,

  /**
   * The current user evaluation.
   */
  userEvaluation: T.shape(
    UserEvaluationTypes.propTypes
  ),

  // the name of the primary action of the resource (if we want to override the default one)
  primaryAction: T.string,

  customActions: T.arrayOf(T.shape(
    ActionTypes.propTypes
  )),
  styles: T.arrayOf(T.string),

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
  children: T.node
}

ResourcePage.defaultProps = {
  path: [],
  routes: []
}

export {
  ResourcePage
}
