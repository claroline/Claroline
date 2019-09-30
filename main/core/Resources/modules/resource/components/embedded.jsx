import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {mount, unmount} from '#/main/app/dom/mount'
import {withReducer} from '#/main/app/store/components/withReducer'
import {Routes} from '#/main/app/router'

import {selectors as configSelectors} from '#/main/app/config/store'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {ResourceMain} from '#/main/core/resource/containers/main'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {actions, reducer, selectors} from '#/main/core/resource/store'

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
    // I'm not sure this is the best way to do it
    // but I need to be able to open a directory directly in the widget
    const RoutedResource = props =>
      <Routes
        path="/desktop/resources"
        routes={[
          {
            path: '/:slug',
            onEnter: (params = {}) => props.openResource(params.slug),
            component: ResourceMain
          }
        ]}
      />

    const ConnectedResource = withReducer(selectors.STORE_NAME, reducer)(
      connect(
        null,
        (dispatch) => ({
          openResource(slug) {
            dispatch(actions.openResource(slug))
          }
        })
      )(RoutedResource)
    )

    ConnectedResource.displayName = `EmbeddedResourceApp(${this.props.resourceNode.meta.type})`

    mount(this.mountNode, ConnectedResource, {}, {
      [securitySelectors.STORE_NAME]: {
        currentUser: this.props.currentUser,
        impersonated: this.props.impersonated
      },
      [configSelectors.STORE_NAME]: this.props.config,
      // mount the resource tool in the store
      tool: {
        loaded: true,
        name: 'resources',
        basePath: '/desktop',
        currentContext: {
          type: 'desktop'
        }
      },
      resources: { // TODO : retrieve tool store name from var
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
