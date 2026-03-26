import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

class ContentEditable extends Component {
  constructor(props) {
    super(props)

    this.state = {}

    this.emitChange = this.emitChange.bind(this)
  }

  shouldComponentUpdate(nextProps) {
    return (
      !this.el
      || (nextProps.value !== this.el.innerHTML
        && nextProps.value !== this.props.value)
    )
  }

  componentDidUpdate() {
    if (this.el && this.props.value !== this.el.innerHTML) {
      this.el.innerHTML = this.props.value
    }
  }

  emitChange() {
    if (!this.el) {
      return
    }

    const content = this.el.innerHTML

    if (this.props.onChange && content !== this.lastContent) {
      this.props.onChange(content)
    }

    this.lastContent = content
  }

  render() {
    return (
      <div
        id={this.props.id}
        className={this.props.className}
        ref={el => this.el = el}
        onInput={this.emitChange}
        onBlur={this.emitChange}
        dangerouslySetInnerHTML={{__html: this.props.value}}
        //placeholder={this.props.placeholder}
        contentEditable={!this.props.disabled}
        role="textbox"

        aria-multiline={true}
      />
    )
  }
}

ContentEditable.propTypes = {
  className: T.string,
  id: T.string,
  value: T.string.isRequired,
  placeholder: T.string,
  onChange: T.func.isRequired,
  disabled: T.bool.isRequired
}

ContentEditable.defaultProps = {
  disabled: false
}

export {
  ContentEditable
}
