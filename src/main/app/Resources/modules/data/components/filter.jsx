import React, {createElement, Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import merge from 'lodash/merge'
import identity from 'lodash/identity'
import isEmpty from 'lodash/isEmpty'

import {makeCancelable} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {getType} from '#/main/app/data/types'

class DataFilter extends Component {
  constructor(props) {
    super(props)

    this.state = {
      error: false,
      loaded: false,
      input: null,
      parse: (value) => value,
      validate: () => undefined
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
          parse: get(result[0], 'parse', identity),
          validate: get(result[0], 'validate'),
          input: get(result[0], 'components.filter') || get(result[0], 'components.search') // components.search is for retro-compatibility
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
        <div role="presentation" className="text-secondary">
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

    if (this.state.input) {
      let errors
      if (this.state.validate) {
        errors = this.state.validate(this.props.value, this.props.options)
      }

      return createElement(this.state.input, merge({}, this.props.options, {
        id: this.props.id,
        className: this.props.className,
        placeholder: this.props.placeholder,
        search: this.props.value, // deprecated use value instead
        value: this.props.value,
        isValid: isEmpty(errors), // deprecated use errors instead
        errors: errors,
        disabled: this.props.disabled,
        size: this.props.size,
        updateSearch: this.props.updateSearch
      }))
    }

    return (
      <input
        id={this.props.id}
        type="text"
        className={classes('data-filter form-control', {
          [`form-control-${this.props.size}`]: !!this.props.size
        })}
        value={this.props.value || ''}
        placeholder={this.props.placeholder}
        disabled={this.props.disabled}
        onChange={(e) => this.props.updateSearch(this.state.parse(e.target.value, this.props.options))}
      />
    )
  }
}

DataFilter.propTypes = {
  id: T.string.isRequired,
  className: T.string,
  type: T.string,
  options: T.object, // depends on the data type
  placeholder: T.string,
  size: T.oneOf(['sm', 'lg']),
  // filter data
  value: T.any, // depends on the data type
  // filter state
  disabled: T.bool,

  updateSearch: T.func.isRequired
}

DataFilter.defaultProps = {
  disabled: false,
  size: 'sm'
}

export {
  DataFilter
}
