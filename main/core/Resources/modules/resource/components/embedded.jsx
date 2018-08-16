import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {mount, unmount} from '#/main/app/mount'

import {App} from '#/main/core/resource'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'

// the class is because of the use of references and lifecycle
class ResourceEmbedded extends Component {
  componentDidMount() {
    this.mountResource(this.props.resourceNode, this.props.lifecycle)
  }

  componentWillReceiveProps(nextProps) {
    // the embedded resource has changed
    if (this.props.resourceNode.id !== nextProps.resourceNode.id) {
      // remove old app
      unmount(this.mountNode)

      this.mountResource(nextProps.resourceNode, nextProps.lifecycle)
    }
  }

  mountResource(resourceNode, lifecycleActions) {
    const ResourceApp = new App()

    mount(this.mountNode, ResourceApp.component, ResourceApp.store, {
      resourceNode: resourceNode,
      embedded: true,
      lifecycle: lifecycleActions
    })
  }

  render() {
    return (
      <div ref={element => this.mountNode = element} className={classes('resource-container embedded-resource-container', this.props.className)} />
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
  })
}

ResourceEmbedded.defaultProps = {
  lifecycle: {}
}

export {
  ResourceEmbedded
}
