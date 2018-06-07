import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import invariant from 'invariant'

import {url} from '#/main/app/api'
import {theme} from '#/main/app/config'
import {mount, unmount} from '#/main/app/mount'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {getResource} from '#/main/core/resources'

class EmbeddedResource extends Component {
  constructor(props) {
    super(props)

    this.state = {}
  }

  componentDidMount() {
    this.load(this.props.resourceNode)
  }

  componentWillReceiveProps(nextProps) {
    // the embedded resource has changed
    if (this.props.resourceNode.id !== nextProps.resourceNode.id) {
      // remove old app
      unmount(this.mountNode)

      // load the new one
      this.load(nextProps.resourceNode)
    }
  }

  componentWillUnmount() {
    unmount(this.mountNode)
  }

  load(resourceNode) {
    // Load app
    getResource(resourceNode.meta.type)()
      .then(module => {
        // Load app data
        fetch(
          url(['claro_resource_load', {type: resourceNode.meta.type, node: resourceNode.id}]), {
            method: 'GET',
            credentials: 'include'
          })
          .then(response => response.json())
          .then((responseData) => {
            // generate the application
            const embeddedApp = module.App()
            if (embeddedApp) {
              this.setState(
                Object.assign({
                  component: null,
                  store: null,
                  styles: null,
                  initialData: (data) => data
                }, embeddedApp),
                () => {
                  // append and bootstrap the app
                  const initialData = this.state.initialData(responseData)

                  // force some values in the embedded store
                  initialData.resource.embedded = true
                  initialData.resource.lifecycle = this.props.lifecycle

                  mount(this.mountNode, this.state.component, this.state.store, initialData)
                }
              )
            }
          })
      })
      .catch(error => {
        // this swallows the original error stack trace
        // and make it complicated to debug but I don't find another way to do it.
        invariant(false, `An error occurred while loading the EmbeddedApp : ${error}`)
      })
  }

  render() {
    return (
      <section className={classes('embedded-resource', this.props.className)}>
        <div ref={element => this.mountNode = element} className={`${this.props.resourceNode.meta.type}-container`} />

        {this.state.styles && 0 !== this.state.styles.length &&
          <link rel="stylesheet" type="text/css" href={theme(this.state.styles)} />
        }
      </section>
    )
  }
}

EmbeddedResource.propTypes = {
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

EmbeddedResource.defaultProps = {
  lifecycle: {}
}

export {
  EmbeddedResource
}
