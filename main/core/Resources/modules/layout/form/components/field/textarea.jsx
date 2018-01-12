import React, {Component} from 'react'
import classes from 'classnames'

import {t} from '#/main/core/translation'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

import {getOffsets} from '#/main/core/scaffolding/text/selection'

// see https://github.com/lovasoa/react-contenteditable
export class ContentEditable extends Component {
  constructor() {
    super()
    this.emitChange = this.emitChange.bind(this)
    this.getSelection = this.getSelection.bind(this)
    this.state = {}
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
        className="form-control"
        aria-multiline={true}
        style={{minHeight: `${this.props.minRows * 32}px`}}
        onMouseUp={this.getSelection}
      />
    )
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
}

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

export class Tinymce extends Component {
  constructor(props) {
    super(props)
    this.editor = null
  }

  componentDidMount() {
    const interval = setInterval(() => {
      const editor = window.tinymce.get(this.props.id)

      if (editor) {
        this.editor = editor
        this.editor.on('mouseup', () => {
          this.getSelection()
        })
        this.editor.on('change', e => {
          const tinyContent = this.editor.getContent()
          const tmp = document.createElement('div')
          tmp.innerHTML = tinyContent

          const offsets = getOffsets(tmp, this.editor.selection.getSel())
          this.props.onChange(e.target.getContent(), offsets)
        })
        this.editor.on('click', e => {
          this.props.onClick(e.target)
        })

        clearInterval(interval)
      }
    }, 100)
  }

  componentWillReceiveProps(nextProps) {
    this.editor.setContent(nextProps.content)
  }

  componentWillUnmount() {
    this.editor.destroy()
  }

  updateText() {
    //nope
  }

  getSelection() {
    const rng = this.editor.selection.getRng()

    this.setState({
      rng: this.editor.selection.getRng().cloneRange(),
      startOffset: rng.startOffset,
      endOffset: rng.endOffset,
      startContainer: rng.startContainer,
      endContainer: rng.endContainer,
      collapsed: rng.collapsed,
      commonAncestorContainer: rng.commonAncestorContainer

    })

    const offsets = getOffsets(this.editor.dom.getRoot(), this.editor.selection.getSel())
    this.props.onSelect(this.editor.selection.getContent(), this.updateText.bind(this), offsets)
  }

  render() {
    return (
      <textarea
        id={this.props.id}
        className="form-control claroline-tiny-mce hide"
        defaultValue={this.props.content}
      />
    )
  }
}

Tinymce.propTypes = {
  id: T.string.isRequired,
  content: T.string.isRequired,
  onChange: T.func.isRequired,
  onSelect: T.func,
  onClick: T.func,
  disabled: T.bool
}

class Textarea extends Component {
  constructor(props) {
    super(props)
    this.state = {minimal: true}
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
      <div className={classes('text-editor', {'minimal': this.state.minimal === true})}>
        <span
          role="button"
          title={t(this.state.minimal ? 'rich_text_tools' : 'minimize')}
          className={classes(
            'toolbar-toggle',
            'fa',
            this.state.minimal ? 'fa-plus-circle' : 'fa-minus-circle'
          )}
          onClick={() => {
            this.setState({minimal: !this.state.minimal})
            this.props.onChangeMode({minimal: !this.state.minimal})
          }}
        />
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
  minRows: T.number,
  onSelect: T.func,
  onClick: T.func,
  onChangeMode: T.func
}, {
  value: '',
  minRows: 2,
  onClick: () => {},
  onSelect: () => {},
  onChangeMode: () => {}
})

export {
  Textarea
}