import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {mount, unmount} from '#/main/app/dom/mount'

import {selectors as configSelectors} from '#/main/app/config/store'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {reducer as contextReducer, selectors as contextSelectors} from '#/main/app/context/store'
import {reducer as toolReducer, selectors as toolSelectors} from '#/main/core/tool/store'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourceWrapper} from '#/main/core/resource/containers/wrapper'

// the class is because of the use of references and lifecycle
class ResourceEmbedded extends Component {
  constructor(props) {
    super(props)

    this.mountedApp = null

    this.mountResource = this.mountResource.bind(this)
  }

  componentDidMount() {
    this.mountResource()
  }

  componentWillUnmount() {
    if (this.mountedApp) {
      // remove old app
      unmount(this.mountedApp)
    }
  }

  componentDidUpdate(prevProps) {
    // the embedded resource has changed
    if (this.props.resourceNode.id !== prevProps.resourceNode.id) {
      // remove old app
      if (this.mountedApp) {
        unmount(this.mountedApp)
        this.mountedApp = null
      }

      setTimeout(this.mountResource, 0)
    }
  }

  mountResource() {
    const Resource = () =>
      <ResourceWrapper slug={this.props.resourceNode.slug} embedded={true} />

    Resource.displayName = `EmbeddedResource(${this.props.resourceNode.meta.type})`

    this.mountedApp = mount(this.mountNode, Resource, {
      [contextSelectors.STORE_NAME]: contextReducer,
      [toolSelectors.STORE_NAME]: toolReducer
    }, {
      [securitySelectors.STORE_NAME]: this.props.security,
      [configSelectors.STORE_NAME]: this.props.config,
      // mount the resource tool in the store
      context: {
        loaded: true,
        type: 'desktop'
      },
      tool: {
        loaded: true,
        name: 'resources'
      },
      resources: {
        root: this.props.resourceNode
      },
      // mount the resource in the store
      resource: {
        embedded: true,
        showHeader: this.props.showHeader,
        lifecycle: this.props.lifecycle
      }
    }, true, `/desktop/resources/${this.props.resourceNode.slug}`)
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

  // from store (to build the embedded store)
  security: T.object,
  config: T.object
}

ResourceEmbedded.defaultProps = {
  lifecycle: {}
}

export {
  ResourceEmbedded
}
