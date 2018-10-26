import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {Action as ActionTypes} from '#/main/app/action/prop-types'

import {
  ResourceNode as ResourceNodeTypes,
  UserEvaluation as UserEvaluationTypes
} from '#/main/core/resource/prop-types'
import {getActions, getToolbar} from '#/main/core/resource/utils'
import {ToolPage} from '#/main/core/tool/containers/page'
import {ResourceIcon} from '#/main/core/resource/components/icon'
import {ResourceRestrictions} from '#/main/core/resource/components/restrictions'
import {UserProgression} from '#/main/core/resource/components/user-progression'

// todo : manage fullscreen through store

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

  componentWillReceiveProps(nextProps) {
    // the resource has changed
    if (this.props.resourceNode.id !== nextProps.resourceNode.id) {
      // load the new one
      this.props.loadResource(nextProps.resourceNode, nextProps.embedded)
    }
  }

  toggleFullscreen() {
    this.setState({fullscreen: !this.state.fullscreen})
  }

  render() {
    return (
      <ToolPage
        className={classes('resource-page', `${this.props.resourceNode.meta.type}-page`)}
        styles={this.props.styles}
        embedded={this.props.embedded}
        showHeader={this.props.embedded ? this.props.showHeader : true}
        fullscreen={this.state.fullscreen}
        title={this.props.resourceNode.name}
        subtitle={this.props.subtitle}
        path={[].concat(this.props.resourceNode.path.map(ancestorNode => ({
          label: ancestorNode.name,
          target: ['claro_resource_show_short', {id: ancestorNode.id}]
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
          update: (resourceNodes) => {
            // checks if the action have modified the current node
            const currentNode = resourceNodes.find(node => node.id === this.props.resourceNode.id)
            if (currentNode) {
              // grabs updated data
              this.props.updateNode(currentNode)
            }
          },
          delete: (resourceNodes) => {
            // checks if the action have deleted the current node
            const currentNode = resourceNodes.find(node => node.id === this.props.resourceNode.id)
            if (currentNode) {
              // grabs updated data
              //this.props.deleteNode(currentNode)
            }
          }
        }).then((actions) => {
          return [].concat(this.props.customActions || [], actions, [
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
          ])
        })}
      >
        {!isEmpty(this.props.accessErrors) &&
          <ResourceRestrictions
            errors={this.props.accessErrors}
            dismiss={this.props.dismissRestrictions}
            managed={this.props.managed}
            checkAccessCode={(code) => this.props.checkAccessCode(this.props.resourceNode, code, this.props.embedded)}
          />
        }

        {this.props.loaded && isEmpty(this.props.accessErrors) &&
          this.props.children
        }
      </ToolPage>
    )
  }
}

ResourcePage.propTypes = {
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
  children: T.node.isRequired
}

ResourcePage.defaultProps = {
  path: []
}

export {
  ResourcePage
}
