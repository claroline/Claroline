import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {PageContainer, PageHeader} from '#/main/core/layout/page'

import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {ResourcePageActions} from '#/main/core/resource/components/page-actions'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {UserProgression} from '#/main/core/resource/components/user-progression'
import {UserEvaluation as UserEvaluationTypes} from '#/main/core/resource/prop-types'

class ResourcePage extends Component {
  constructor(props) {
    super(props)

    // open resource in fullscreen if configured
    this.state = {
      fullscreen: !this.props.embedded && this.props.resourceNode.display.fullscreen
    }

    this.toggleFullscreen = this.toggleFullscreen.bind(this)

    /*{
     name: 'fullscreen',
     type: 'callback',
     icon: classes('fa fa-fw', {
     'fa-expand': !this.state.fullscreen,
     'fa-compress': this.state.fullscreen
     }),
     label: trans(this.state.fullscreen ? 'fullscreen_off' : 'fullscreen_on'),
     callback: this.toggleFullscreen
     }*/
  }

  toggleFullscreen() {
    this.setState({fullscreen: !this.state.fullscreen})
  }

  render() {
    return (
      <PageContainer
        embedded={this.props.embedded}
        fullscreen={this.state.fullscreen}
      >
        {!this.props.embedded &&
          <PageHeader
            title={this.props.resourceNode.name}
            poster={this.props.resourceNode.poster ? this.props.resourceNode.poster.url : undefined}
          >
            {this.props.resourceNode.display.showIcon && this.props.userEvaluation &&
              <UserProgression
                userEvaluation={this.props.userEvaluation}
                width={70}
                height={70}
              />
            }

            <ResourcePageActions
              resourceNode={this.props.resourceNode}
              editor={this.props.editor}
              customActions={this.props.customActions}
              fullscreen={this.state.fullscreen}
              toggleFullscreen={this.toggleFullscreen}
              togglePublication={this.props.togglePublication}
              updateNode={this.props.updateNode}
              toggleNotifications={this.props.toggleNotifications}
            />
          </PageHeader>
        }

        {this.props.children}
      </PageContainer>
    )
  }
}

ResourcePage.propTypes = {
  /**
   * The current resource node.
   */
  resourceNode: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired,

  /**
   * The current user evaluation.
   */
  userEvaluation: T.shape(
    UserEvaluationTypes.propTypes
  ),

  customActions: T.arrayOf(T.shape(
    ActionTypes.propTypes
  )),

  /**
   * If provided, this permits to manage the resource editor in the header (aka. open, save actions).
   */
  editor: T.shape({
    icon: T.string,
    label: T.string,
    opened: T.bool,
    open: T.oneOfType([T.func, T.string]),
    path: T.string,
    save: T.shape({
      disabled: T.bool.isRequired,
      action: T.oneOfType([T.string, T.func]).isRequired
    }).isRequired
  }),

  togglePublication: T.func.isRequired,
  updateNode: T.func.isRequired,

  // todo : reuse Page propTypes
  embedded: T.bool,
  children: T.node.isRequired,

  // resource notification
  toggleNotifications: T.func
}

export {
  ResourcePage
}
