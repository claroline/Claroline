import React, {Component} from 'react'
import classes from 'classnames'

import {PropTypes as T} from '#/main/app/prop-types'

import {getOffsets} from '#/main/core/scaffolding/text/selection'

// see https://github.com/lovasoa/react-contenteditable
class ContentEditable extends Component {
  constructor(props) {
    super(props)

    this.state = {}

    this.emitChange = this.emitChange.bind(this)
    this.getSelection = this.getSelection.bind(this)
  }

  getSelection() {
    //http://stackoverflow.com/questions/3997659/replace-selected-text-in-contenteditable-div
    const rng = window.getSelection().getRangeAt(0).cloneRange()
    this.setState({
      rng: rng,
      startOffset: rng.startOffset,
      endOffset: rng.endOffset,
      startContainer: rng.startContainer,
      endContainer: rng.endContainer,
      collapsed: rng.collapsed,
      commonAncestorContainer: rng.commonAncestorContainer
    })

    const selected = window.getSelection().toString()
    const offsets = getOffsets(document.getElementById(this.props.id))

    this.props.onSelect(selected, this.updateText.bind(this), offsets)
  }

  updateText(text) {
    if (text) {
      const range = new Range()
      range.setStart(this.state.startContainer, this.state.startOffset)
      range.setEnd(this.state.endContainer, this.state.endOffset)
      range.deleteContents()
      const el = document.createElement('span')
      el.innerHTML = text
      range.insertNode(el)

      return this.el.innerHTML
    }
  }

  componentDidMount() {
    this.el.onclick = e => {
      this.props.onClick(e.target)
    }
  }

  shouldComponentUpdate(nextProps) {
    return (
      !this.el
      || (nextProps.content !== this.el.innerHTML
        && nextProps.content !== this.props.content)
    )
  }

  componentDidUpdate() {
    if (this.el && this.props.content !== this.el.innerHTML) {
      this.el.innerHTML = this.props.content
    }
  }

  emitChange() {
    if (!this.el) {
      return
    }

    const content = this.el.innerHTML
    const offsets = getOffsets(document.getElementById(this.props.id))

    if (this.props.onChange && content !== this.lastContent) {
      this.props.onChange(content, offsets)
    }

    this.lastContent = content
  }

  render() {
    return (
      <div
        id={this.props.id}
        ref={el => this.el = el}
        onInput={this.emitChange}
        onBlur={this.emitChange}
        dangerouslySetInnerHTML={{__html: this.props.content}}
        placeholder={this.props.placeholder}
        contentEditable={!this.props.disabled}
        role="textbox"
        className={classes('form-control', {
          [`input-${this.props.size}`]: !!this.props.size,
          disabled: this.props.disabled
        })}
        aria-multiline={true}
        style={{
          minHeight: `${this.props.minRows * 34}px`
        }}
        onMouseUp={this.getSelection}
      />
    )
  }
}

ContentEditable.propTypes = {
  id: T.string.isRequired,
  size: T.oneOf(['sm', 'lg']),
  minRows: T.number.isRequired,
  content: T.string.isRequired,
  placeholder: T.string,
  onChange: T.func.isRequired,
  onSelect: T.func,
  onClick: T.func,
  disabled: T.bool.isRequired
}

ContentEditable.defaultProps = {
  onClick: () => {},
  onSelect: () => {},
  minRows: 1,
  disabled: false
}

export {
  ContentEditable
}