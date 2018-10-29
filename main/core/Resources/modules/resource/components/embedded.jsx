import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {mount, unmount} from '#/main/app/mount'

import {constants} from '#/main/core/tool/constants'
import {App} from '#/main/core/resource'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'

// the class is because of the use of references and lifecycle
class ResourceEmbedded extends Component {
  componentDidMount() {
    this.mountResource(this.props.resourceNode, this.props.lifecycle, this.props.showHeader)
  }

  componentWillReceiveProps(nextProps) {
    // the embedded resource has changed
    if (this.props.resourceNode.id !== nextProps.resourceNode.id) {
      // remove old app
      unmount(this.mountNode)
      this.props.onResourceClose(this.props.resourceNode.id)

      this.mountResource(nextProps.resourceNode, nextProps.lifecycle, nextProps.showHeader)
    }
  }

  componentWillUnmount() {
    this.props.onResourceClose(this.props.resourceNode.id)
  }

  mountResource(resourceNode, lifecycleActions, showHeader) {
    const ResourceApp = new App()

    mount(this.mountNode, ResourceApp.component, ResourceApp.store, {
      tool: {
        name: 'resource_manager',
        context: {
          type: resourceNode.workspace ? constants.TOOL_WORKSPACE : constants.TOOL_DESKTOP,
          data: resourceNode.workspace || null
        }
      },
      resourceNode: resourceNode,
      embedded: true,
      showHeader: showHeader,
      lifecycle: lifecycleActions
    })
  }

  render() {
    return (
      <div ref={element => this.mountNode = element} className={classes('resource-container embedded-resource', this.props.className)} />
    )
  }
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
  onResourceClose: T.func.isRequired
}

ResourceEmbedded.defaultProps = {
  lifecycle: {},
  onResourceClose: () => {}
}

export {
  ResourceEmbedded
}
