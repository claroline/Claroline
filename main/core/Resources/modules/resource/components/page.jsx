import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/core/translation'
import {Page} from '#/main/app/page/components/page'
import {Action as ActionTypes} from '#/main/app/action/prop-types'

import {UserEvaluation as UserEvaluationTypes} from '#/main/core/resource/prop-types'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {getActions, getToolbar} from '#/main/core/resource/utils'

import {UserProgression} from '#/main/core/resource/components/user-progression'

class ResourcePage extends Component {
  constructor(props) {
    super(props)

    // open resource in fullscreen if configured
    this.state = {
      fullscreen: !this.props.embedded && this.props.resourceNode.display.fullscreen
    }
  }

  toggleFullscreen() {
    this.setState({fullscreen: !this.state.fullscreen})
  }

  render() {
    return (
      <Page
        embedded={this.props.embedded}
        fullscreen={this.state.fullscreen}
        title={this.props.resourceNode.name}
        poster={this.props.resourceNode.poster ? this.props.resourceNode.poster.url : undefined}
        icon={this.props.resourceNode.display.showIcon && this.props.userEvaluation &&
          <UserProgression
            userEvaluation={this.props.userEvaluation}
            width={70}
            height={70}
          />
        }
        toolbar={getToolbar(this.props.primaryAction, true)}
        actions={getActions([this.props.resourceNode]).then((actions) => {
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
        {this.props.children}
      </Page>
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

  // the name of the primary action of the resource (if we want to override the default one)
  primaryAction: T.string,

  customActions: T.arrayOf(T.shape(
    ActionTypes.propTypes
  )),

  // todo : reuse Page propTypes
  embedded: T.bool,
  children: T.node.isRequired
}

export {
  ResourcePage
}
