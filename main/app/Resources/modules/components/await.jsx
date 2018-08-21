import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import isEqual from 'lodash/isEqual'

import {makeCancelable} from '#/main/app/api'

class Await extends Component {
  constructor(props) {
    super(props)

    this.state = {
      status: 'pending',
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
          (results) => {
            if (this.props.then) {
              this.props.then(results)
            }

            this.setState({status: 'success'})
          },
          (error) => {
            if (typeof error !== 'object' || !error.isCanceled) {
              this.setState({
                status: 'error',
                error: error
              })

              // TODO : find better. I don't understand why invariant is not thrown
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
        return this.props.children || null

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
  then: T.func,

  /**
   * The placeholder to display while waiting.
   */
  placeholder: T.node,
  children: T.node
}

export {
  Await
}
