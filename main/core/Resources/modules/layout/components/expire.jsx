import {Component} from 'react'
import {PropTypes as T} from 'prop-types'

class Expire extends Component {
  componentWillReceiveProps(nextProps) {
    // reset the timer if children are changed
    if (nextProps.children !== this.props.children) {
      this.setTimer()
    }
  }

  componentDidMount() {
    this.setTimer()
  }


  componentWillUnmount() {
    clearTimeout(this._timer)
  }

  setTimer() {
    // clear any existing timer
    this._timer != null ? clearTimeout(this._timer) : null

    // execute callback after `delay` milliseconds
    this._timer = setTimeout(function () {
      this.props.onExpire()
      this._timer = null
    }.bind(this), this.props.delay)
  }

  render() {
    return this.props.children
  }
}

Expire.propTypes = {
  delay: T.number,
  onExpire: T.func.isRequired,
  children: T.element.isRequired
}

Expire.defaultProps = {
  delay: 1000,
  onExpire: T.func.isRequired,
  children: T.element.isRequired
}

export {
  Expire
}
