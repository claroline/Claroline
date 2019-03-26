import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

class AnimationTypewriting extends Component {
  constructor(props) {
    super(props)

    this.state = {
      written: ''
    }

    this.write = this.write.bind(this)
  }

  write() {
    if (this.state.written !== this.props.text) {
      const rest = this.props.text.replace(this.state.written, '')

      this.setState({
        written: this.state.written + rest[0]
      })

      this.writer = setTimeout(this.write, 150)
    }
  }

  componentDidMount() {
    this.write()
  }

  componentWillUnmount() {
    clearTimeout(this.writer)
  }

  render() {
    return this.state.written
  }
}

AnimationTypewriting.propTypes = {
  text: T.string.isRequired
}

export {
  AnimationTypewriting
}