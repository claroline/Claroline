import React, {createElement, Component} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import merge from 'lodash/merge'

import {makeCancelable} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {getType} from '#/main/app/data/types'

class DataDisplay extends Component {
  constructor(props) {
    super(props)

    this.state = {
      error: false,
      loaded: false,
      display: null,
      render: null
    }
  }

  static getDerivedStateFromError() {
    // Update state so the next render will show the fallback UI.
    return {
      error: true,
      loaded: true
    }
  }

  componentDidMount() {
    this.load()
  }

  componentDidUpdate(prevProps) {
    if (prevProps.type !== this.props.type) {
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
    if (this.pending) {
      this.pending.cancel()
      this.pending = null
    }

    this.pending = makeCancelable(
      Promise.all([
        this.props.type ? getType(this.props.type) : Promise.resolve({})
      ])
    )

    this.pending.promise
      .then(
        (result = []) => this.setState({
          loaded: true,
          display: get(result[0], 'components.display'),
          render: get(result[0], 'render')
        })
      )
      .then(
        () => this.pending = null,
        (error) => {
          this.pending = null
          this.setState({loaded: true, error: error})
        }
      )
  }

  render() {
    if (!this.state.loaded) {
      return (
        <div role="presentation" className="text-body-secondary">
          {trans('loading')}
        </div>
      )
    }

    if (this.state.error) {
      return (
        <div role="presentation" className="text-danger">
          {trans('error')}
        </div>
      )
    }

    if (this.props.children) {
      return this.props.children
    }

    if (this.props.render) {
      return this.props.render(this.props.value, this.props.error)
    }

    if (this.state.display) {
      return createElement(this.state.display,
        // the props to pass to the input
        merge({}, this.props.options || {}, {
          data: this.props.value, // deprecated
          value: this.props.value,
          placeholder: this.props.placeholder,
          size: this.props.size
        })
      )
    }

    if (!this.props.value && false !== this.props.value && 0 !== this.props.value) {
      return this.props.placeholder || (
        <em role="presentation" className="text-body-tertiary">{trans('empty_value')}</em>
      )
    }

    if (this.state.render) {
      // type render method
      return this.state.render(this.props.value, this.props.options || {})
    }

    return this.props.value
  }
}

DataDisplay.propTypes = {
  type: T.string,
  options: T.object, // depends on the data type
  placeholder: T.any, // depends on the data type
  size: T.oneOf(['sm', 'lg']),

  // field data
  value: T.any, // depends on the data type
  error: T.oneOfType([
    T.string,
    T.arrayOf(T.string),
    T.object
  ]),

  // customization
  // It will replace the render of the input.
  children: T.node,
  render: T.func
}

export {
  DataDisplay
}
