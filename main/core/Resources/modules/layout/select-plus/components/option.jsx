import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {trans} from '#/main/core/translation'

class Option extends Component {
  constructor(props) {
    super(props)
    
    this.handleOnClick = this.handleOnClick.bind(this)
  }
  
  handleOnClick(evt) {
    evt.preventDefault()
    evt.stopPropagation()
    this.props.onSelect(this.props.value, this.props.label, evt)
  }
  
  render() {
    const props = this.props
    return (
      <div
        className="select-plus-option"
        onClick={this.handleOnClick}
      >
        { props.transDomain ?
          trans(props.label, {}, props.transDomain) :
          props.label
        }
      </div>
    )
  }
}

Option.propTypes = {
  label: T.string.isRequired,
  value: T.any.isRequired,
  onSelect: T.func.isRequired,
  transDomain: T.string
}

Option.defaultProps = {
  transDomain: null
}

export {
  Option
}