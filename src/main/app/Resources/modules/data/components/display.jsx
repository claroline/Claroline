import React, {createElement, Component} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import merge from 'lodash/merge'

import {makeCancelable} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {getType} from '#/main/app/data/types'

import {FormGroup} from '#/main/app/content/form/components/group'

// todo : add loading placeholder
// todo : better error handling on undefined types

class DataDisplay extends Component {
  constructor(props) {
    super(props)

    this.state = {
      error: false,
      loaded: false,
      input: null,
      group: null,
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
          group: get(result[0], 'components.group'),
          display: get(result[0], 'components.display') || get(result[0], 'components.details'), // components.details is for retro-compatibility
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

  renderInput() {
    /*{(!props.value && false !== props.value) &&
    <span className="data-details-empty">{trans('empty_value')}</span>
    }*/
    if (!this.state.loaded) {
      return trans('loading')
    }

    if (this.state.error) {
      return trans('error')
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
        merge({}, this.props.options, {
          id: this.props.id,
          label: this.props.label,
          data: this.props.value, // todo rename into `value` in implementations later
          error: this.props.error,
          placeholder: this.props.placeholder,
          size: this.props.size
        })
      )
    }

    if (!this.props.value && false !== this.props.value && 0 !== this.props.value) {
      return (
        <span className="data-details-empty">{trans('empty_value')}</span>
      )
    }

    if (this.state.render) {
      // type render method
      return this.state.render(this.props.value, this.props.options || {})
    }

    return this.props.value
  }

  render() {
    // the group component to create
    return createElement(this.state.group || FormGroup,
      // the props to pass to the group
      {
        id: this.props.id,
        label: this.props.label,
        hideLabel: this.props.hideLabel,
        help: this.props.help,
        error: this.props.error
      },
      this.renderInput()
    )
  }
}

DataDisplay.propTypes = {
  id: T.string.isRequired,
  type: T.string,
  label: T.string.isRequired,
  hideLabel: T.bool,
  options: T.object, // depends on the data type
  help: T.oneOfType([T.string, T.arrayOf(T.string)]),
  placeholder: T.any, // depends on the data type
  size: T.oneOf(['sm', 'lg']),

  // field data
  value: T.any, // depends on the data type
  error: T.oneOfType([
    T.string,
    T.arrayOf(T.string),
    T.arrayOf(T.arrayOf(T.string)),
    T.object
  ]),

  // customization
  // It will replace the render of the input.
  children: T.node,
  render: T.func
}

DataDisplay.defaultProps = {
  hideLabel: false,
  options: {},
  required: false,
  disabled: false,
  validating: false
}

export {
  DataDisplay
}
