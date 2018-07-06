import {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import isEqual from 'lodash/isEqual'

import {makeCancelable} from '#/main/app/api'

class Await extends Component {
  constructor(props) {
    super(props)

    this.state = {
      status: 'pending'
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

  load() {
    if (!this.pending) {
      this.pending = makeCancelable(
        this.props.for
          .then((results) => {
            if (this.props.then) {
              this.props.then(results)
            }

            this.setState({status: 'success'})
          })
          .catch(() => this.setState({status: 'error'}))
      )

      this.pending.promise.then(
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
        return this.props.error || null
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
  error: T.node,
  children: T.node
}

export {
  Await
}