import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import isEqual from 'lodash/isEqual'

import {makeCancelable} from '#/main/app/api/fetch/makeCancelable'

class Await extends Component {
  constructor(props) {
    super(props)

    this.state = {
      status: 'pending',
      result: null,
      error: null
    }
  }

  componentDidMount() {
    this.load()
  }

  componentDidUpdate(prevProps) {
    if (!isEqual(prevProps.for, this.props.for)) {
      this.load()
    }
  }

  componentWillUnmount() {
    if (this.pending) {
      this.pending.cancel()
      this.pending = null
    }
  }

  load() {
    if (!this.pending) {
      this.pending = makeCancelable(this.props.for)

      this.pending.promise
        .then(
          (resolved) => {
            let result = null
            if (this.props.then) {
              result = this.props.then(resolved) || null
            }

            this.setState({
              status: 'success',
              result: result,
              error: null
            })
          },
          (error) => {
            if (typeof error !== 'object' || !error.isCanceled) {
              this.setState({
                status: 'error',
                result: null,
                error: error
              })

              // TODO : find better.
              /* eslint-disable no-console */
              console.error(error)
              /* eslint-enable no-console */
            }
          }
        )
        .then(
          () => this.pending = null,
          () => this.pending = null
        )
    }
  }

  render() {
    switch (this.state.status) {
      case 'pending':
        return this.props.placeholder || null

      case 'success':
        return this.state.result

      case 'error':
        return (
          <div className="alert alert-danger">
            <b>{this.state.error.message}</b>
            <p>{this.state.error.stack}</p>
          </div>
        )
    }

    return null
  }
}

Await.propTypes = {
  /**
   * The promise to await for.
   *
   * @type {Promise}
   */
  for: T.shape({
    then: T.func.isRequired,
    catch: T.func.isRequired
  }),

  /**
   * A callback which will be called with the promised results.
   * NB. The return value of this function will be rendered.
   *
   * @type {func}
   */
  then: T.func,

  /**
   * The placeholder to display while waiting.
   */
  placeholder: T.node
}

export {
  Await
}
