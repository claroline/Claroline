import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import invariant from 'invariant'
import isEqual from 'lodash/isEqual'

import {bootstrap} from '#/main/app/bootstrap'
import {unmount} from '#/main/app/mount'
import {theme} from '#/main/app/config'

/**
 * Mounts an entire React application (components + store) inside another.
 *
 * For now it's not possible for the 2 apps to communicate.
 *
 * @todo add loading
 */
class Embedded extends Component {
  constructor(props) {
    super(props)

    this.state = {}
    this.mountNode
  }

  load() {
    this.props.load()
      .then(module => {
        // generate the application
        const embeddedApp = module.App(...this.props.parameters)
        if (embeddedApp) {
          this.setState(embeddedApp, () => {
            // append and bootstrap the app
            bootstrap(`.${this.props.name}-container`, this.state.component, this.state.store, this.state.initialData)
          })
        }
      })
      .catch(error => {
        // this swallows the original error stack trace
        // and make it complicated to debug but I don't find another way to do it.
        invariant(false, `An error occurred while loading the EmbeddedApp : ${error}`)
      })
  }

  componentDidMount() {
    this.load()
  }

  componentDidUpdate(prevProps) {
    // the app have changed, we need to reload it
    if (prevProps.name !== this.props.name || !isEqual(prevProps.parameters, this.props.parameters)) {
      // we need to destroy the old one before
      unmount(this.mountNode)

      // load new app
      this.load()
    }
  }

  componentWillUnmount() {
    // remove embedded app when component is removed
    unmount(this.mountNode)
  }

  render() {
    return (
      <sections className="embedded-app">
        <div className={`${this.props.name}-container`} ref={element => this.mountNode = element} />

        {this.state.styles && 0 !== this.state.styles.length &&
          <link rel="stylesheet" type="text/css" href={theme(this.state.styles)} />
        }
      </sections>
    )
  }
}


Embedded.propTypes = {
  name: T.string.isRequired,
  load: T.func.isRequired,
  parameters: T.array
}

Embedded.defaultProps = {
  parameters: []
}

export {
  Embedded
}
