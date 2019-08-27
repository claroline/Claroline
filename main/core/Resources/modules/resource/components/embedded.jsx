import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {mount, unmount} from '#/main/app/dom/mount'

import {selectors as configSelectors} from '#/main/app/config/store'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {ResourceMain} from '#/main/core/resource/containers/main'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'

// the class is because of the use of references and lifecycle
class ResourceEmbedded extends Component {
  constructor(props) {
    super(props)

    this.mountResource = this.mountResource.bind(this)
  }

  componentDidMount() {
    this.mountResource()
  }

  componentDidUpdate(prevProps) {
    // the embedded resource has changed
    if (this.props.resourceNode.id !== prevProps.resourceNode.id) {
      // remove old app
      unmount(this.mountNode)
      this.props.onResourceClose(prevProps.resourceNode.id)

      setTimeout(this.mountResource, 0)
    }
  }

  componentWillUnmount() {
    this.props.onResourceClose(this.props.resourceNode.id)
    // remove old app
    unmount(this.mountNode)
  }

  mountResource() {
    mount(this.mountNode, ResourceMain, {}, {
      [securitySelectors.STORE_NAME]: {
        currentUser: this.props.currentUser,
        impersonated: this.props.impersonated
      },
      [configSelectors.STORE_NAME]: this.props.config,
      tool: {
        loaded: true,
        name: 'resource_manager',
        basePath: '',
        currentContext: {
          type: 'desktop'
        }
      },
      resource: {
        slug: this.props.resourceNode.slug,
        embedded: true,
        showHeader: this.props.showHeader,
        lifecycle: this.props.lifecycle
      }
    }, true, `/resource_manager/${this.props.resourceNode.slug}`)
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
  onResourceClose: T.func.isRequired,

  // from store (to build the embedded store)
  currentUser: T.object,
  impersonated: T.bool,
  config: T.object
}

ResourceEmbedded.defaultProps = {
  lifecycle: {},
  onResourceClose: () => true
}

export {
  ResourceEmbedded
}
