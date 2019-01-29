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
    this.mountResource()
  }

  componentDidUpdate(prevProps) {
    // the embedded resource has changed
    if (this.props.resourceNode.id !== prevProps.resourceNode.id) {
      // remove old app
      unmount(this.mountNode)
      this.props.onResourceClose(prevProps.resourceNode.id)

      // FIXME : otherwise the new app is not correctly booted and I don't know why
      setTimeout(this.mountResource.bind(this), 0)
      //this.mountResource()
    }
  }

  componentWillUnmount() {
    this.props.onResourceClose(this.props.resourceNode.id)
    // remove old app
    unmount(this.mountNode)
  }

  mountResource() {
    const ResourceApp = new App()

    mount(this.mountNode, ResourceApp.component, ResourceApp.store, {
      tool: {
        name: 'resource_manager',
        // In fact, I think I should let the caller choose the context
        currentContext: {
          type: this.props.resourceNode.workspace ? constants.TOOL_WORKSPACE : constants.TOOL_DESKTOP,
          data: this.props.resourceNode.workspace || null
        }
      },
      resourceNode: this.props.resourceNode,
      embedded: true,
      showHeader: this.props.showHeader,
      lifecycle: this.props.lifecycle
    }, true)
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
  onResourceClose: () => true
}

export {
  ResourceEmbedded
}
