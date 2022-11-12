import React, {Component} from 'react'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'

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

// TODO : manage max height like TinyMCE and CodeMirror
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

class HtmlInput extends Component {
  constructor(props) {
    super(props)

    this.state = {
      minimal: props.minimal
    }

    this.toggleEditor = this.toggleEditor.bind(this)
  }

  makeMinimalEditor() {
    return (
      <ContentEditable
        id={this.props.id}
        size={this.props.size}
        minRows={this.props.minRows}
        placeholder={this.props.placeholder}
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
        placeholder={this.props.placeholder}
        content={this.props.value || ''}
        workspace={this.props.workspace}
        disabled={this.props.disabled}
        onChange={this.props.onChange}
        onSelect={this.props.onSelect}
        onClick={this.props.onClick}
      />
    )
  }

  toggleEditor() {
    this.setState({minimal: !this.state.minimal})
    this.props.onChangeMode({minimal: !this.state.minimal})
  }

  render() {
    return (
      <div id={`${this.props.id}-container`} className={classes('editor-control text-editor', this.props.className, {
        minimal: this.state.minimal
      })}>
        <Button
          id={`${this.props.id}-editor-toggle`}
          className="btn-link toolbar-toggle"
          type={CALLBACK_BUTTON}
          icon={classes('fa', {
            'fa-plus-circle': this.state.minimal,
            'fa-minus-circle': !this.state.minimal
          })}
          label={trans(this.state.minimal ? 'show_editor_toolbar' : 'hide_editor_toolbar')}
          tooltip="left"
          callback={this.toggleEditor}
        />

        {this.state.minimal ?
          this.makeMinimalEditor() :
          this.makeFullEditor()
        }
      </div>
    )
  }
}

implementPropTypes(HtmlInput, DataInputTypes, {
  // more precise value type
  value: T.string,
  // custom props
  minimal: T.bool,
  minRows: T.number,
  workspace: T.object,
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
  HtmlInput
}