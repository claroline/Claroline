import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import invariant from 'invariant'

import {bootstrap} from '#/main/app/bootstrap'
import {theme} from '#/main/core/scaffolding/asset'

/**
 * Mounts an entire React application (components + store) inside another.
 *
 * For instance it's not possible for the 2 apps to communicate.
 *
 * @todo add loading
 */
class Embedded extends Component {
  constructor(props) {
    super(props)

    this.state = {}
  }

  componentDidMount() {
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

  componentWillUnmount() {}

  render() {
    return (
      <sections className="embedded-app">
        <div className={`${this.props.name}-container`} />

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
