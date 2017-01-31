import React, {Component, PropTypes as T} from 'react'
import debounce from 'lodash/debounce'
import {FormGroup} from './form-group.jsx'

/******
 ** WARNING: kept for future use -- changes/tests are needed
 **/

export class Text extends Component {
  constructor(props) {
    super(props)
    this.lastPropValue = props.input.value
    this.state = {value: props.input.value}
    this.debouncedOnChange = debounce(event => {
      props.input.onChange(event.target.value)
    }, 200)
    this.handleChange = event => {
      event.persist()
      this.setState({value: event.target.value})
      this.debouncedOnChange(event)
    }
  }

  getValue() {
    const value = this.props.input.value !== this.lastPropValue ?
      this.props.input.value :
      this.state.value
    this.lastPropValue = this.props.input.value

    return value
  }

  render() {
    return (
      <FormGroup {...this.props}>
        <input
          id={this.props.input.name}
          name={this.props.input.name}
          className="form-control"
          type="text"
          value={this.getValue()}
          onChange={this.handleChange}
          aria-describedby={this.props.input.name}/>
      </FormGroup>
    )
  }
}

Text.propTypes = {
  input: T.shape({
    name: T.string.isRequired,
    value: T.string.isRequired,
    onChange: T.func.isRequired
  }).isRequired,
  help: T.string
}
