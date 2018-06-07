import {Component} from 'react'
import {PropTypes as T} from 'prop-types'

class Await extends Component {
  constructor(props) {
    super(props)

    this.state = {
      status: 'pending'
    }
  }

  componentDidMount() {
    this.props.for
      .then((results) => {
        if (this.props.then) {
          this.props.then(results)
        }

        this.setState({status: 'success'})
      })
      .catch(() => this.setState({status: 'error'}))
  }

  render() {
    switch (this.state.status) {
      case 'pending':
        return this.props.placeholder

      case 'success':
        return this.props.children

      case 'error':
        return this.props.error || null
    }
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
  children: T.node.isRequired
}

export {
  Await
}