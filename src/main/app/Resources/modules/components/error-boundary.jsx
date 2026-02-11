import React from 'react'
import {PropTypes as T} from 'prop-types'

class ErrorBoundary extends React.Component {
  constructor(props) {
    super(props)

    this.state = { hasError: false }
  }

  static getDerivedStateFromError() {
    // Update state so the next render will show the fallback UI.
    return { hasError: true }
  }

  render() {
    if (this.state.hasError) {
      // You can render any custom fallback UI
      return this.props.fallback
    }

    return this.props.children
  }
}

ErrorBoundary.propTypes = {
  fallback: T.any,
  children: T.any
}

export {
  ErrorBoundary
}
