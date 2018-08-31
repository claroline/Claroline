import React, {Component} from 'react'
import classes from 'classnames'

import {trans} from '#/main/core/translation'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {TooltipButton} from '#/main/core/layout/button/components/tooltip-button.jsx'

import {Tinymce} from '#/main/core/tinymce/components/tinymce'

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
        contentEditable={!this.props.disabled}
        role="textbox"
        className={classes('form-control', {
          disabled: this.props.disabled
        })}
        aria-multiline={true}
        style={{minHeight: `${this.props.minRows * 32}px`}}
        onMouseUp={this.getSelection}
      />
    )
  }
}

// TODO : manage max height like TinyMCE and CodeMirror
ContentEditable.propTypes = {
  id: T.string.isRequired,
  minRows: T.number.isRequired,
  content: T.string.isRequired,
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

class Textarea extends Component {
  constructor(props) {
    super(props)
    this.state = {minimal: props.minimal}
  }

  makeMinimalEditor() {
    return (
      <ContentEditable
        id={this.props.id}
        minRows={this.props.minRows}
        content={this.props.value || ''}
        disabled={this.props.disabled}
        onChange={this.props.onChange}
        onSelect={this.props.onSelect}
        onClick={this.props.onClick}
      />
    )
  }

  makeFullEditor() {
    return (
      <Tinymce
        id={this.props.id}
        content={this.props.value || ''}
        disabled={this.props.disabled}
        onChange={this.props.onChange}
        onSelect={this.props.onSelect}
        onClick={this.props.onClick}
      />
    )
  }

  render() {
    return (
      <div id={`${this.props.id}-container`} className={classes('editor-control text-editor', {
        minimal: this.state.minimal
      })}>
        <TooltipButton
          id={`${this.props.id}-editor-toggle`}
          title={trans(this.state.minimal ? 'show_editor_toolbar' : 'hide_editor_toolbar')}
          position="left"
          className="toolbar-toggle"
          onClick={() => {
            this.setState({minimal: !this.state.minimal})
            this.props.onChangeMode({minimal: !this.state.minimal})
          }}
        >
          <span className={classes('fa', {
            'fa-plus-circle': this.state.minimal,
            'fa-minus-circle': !this.state.minimal
          })} />
        </TooltipButton>

        {this.state.minimal ?
          this.makeMinimalEditor() :
          this.makeFullEditor()
        }
      </div>
    )
  }
}

implementPropTypes(Textarea, FormFieldTypes, {
  // more precise value type
  value: T.string,
  // custom props
  minimal: T.bool,
  minRows: T.number,
  onSelect: T.func,
  onClick: T.func,
  onChangeMode: T.func
}, {
  value: '',
  minRows: 2,
  minimal: true,
  onClick: () => {},
  onSelect: () => {},
  onChangeMode: () => {}
})

export {
  Textarea
}