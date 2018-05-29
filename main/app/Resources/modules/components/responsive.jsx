/* global window */

import {Component} from 'react'
import {PropTypes as T} from 'prop-types'

// taken from bootstrap
const SCREEN_XS_MAX = 768
const SCREEN_SM_MAX = 992
const SCREEN_MD_MAX = 1200

const SIZE_XS = 'xs'
const SIZE_SM = 'sm'
const SIZE_MD = 'md'
const SIZE_LG = 'lg'

/**
 * A component that have different rendering based on
 * the current window size.
 *
 * NB. It's experimental for now, call @Elorfin if you want to use it.
 */
class Responsive extends Component
{
  constructor(props) {
    super(props)

    this.state = {
      computedSize: this.computeSize()
    }

    this.computeSize = this.computeSize.bind(this)
    this.resize = this.resize.bind(this)
  }

  componentDidMount() {
    window.addEventListener('resize', this.resize)
  }

  componentWillUnmount() {
    window.removeEventListener('resize', this.resize)
  }

  computeSize() {
    // todo maybe store the value inside locale storage or elsewhere for booting
    // I expect, on mount, the rendering size is not properly calculated with this one

    let newSize
    if (window.innerWidth < SCREEN_XS_MAX) {
      // XS screen detected
      newSize = SIZE_XS
    } else if (window.innerWidth < SCREEN_SM_MAX) {
      // SM screen detected
      newSize = SIZE_SM
    } else if (window.innerWidth < SCREEN_MD_MAX) {
      // MD screen detected
      newSize = SIZE_MD
    } else {
      // LG screen detected
      newSize = SIZE_LG
    }

    return newSize
  }

  resize() {
    const newSize = this.computeSize()
    if (newSize !== this.state.computedSize) {
      this.setState({computedSize: newSize})
    }
  }

  render() {
    // renders the defined component for the current screen size
    return (this.props[this.state.computedSize] || this.props.default)
  }
}

Responsive.propTypes = {
  default: T.node.isRequired,
  xs: T.node,
  sm: T.node,
  md: T.node,
  lg: T.node
}

export {
  Responsive
}
