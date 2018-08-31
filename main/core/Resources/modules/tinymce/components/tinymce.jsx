import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {withModal} from '#/main/app/overlay/modal/withModal'

import {tinymce} from '#/main/core/tinymce'
import {config} from '#/main/core/tinymce/config'

import {getOffsets} from '#/main/core/scaffolding/text/selection'

// todo : correct management of container
// todo : allow to add custom CSS
class Editor extends Component {
  constructor(props) {
    super(props)

    this.editor = null
  }

  componentDidMount() {
    tinymce.init(
      Object.assign({}, config, {
        target: this.textarea,
        ui_container: `#${this.props.id}-container`,

        // give access to the show modal action to tinymce plugins
        // it's not really aesthetic but there is no other way
        showModal: this.props.showModal
      })
    )

    this.editor = tinymce.get(this.props.id)
    tinymce.setActive(this.editor)

    this.editor.on('mouseup', () => {
      this.getSelection()
    })

    this.editor.on('change', () => {
      const tinyContent = this.editor.getContent()
      const tmp = document.createElement('div')
      tmp.innerHTML = tinyContent

      const offsets = getOffsets(tmp, this.editor.selection.getSel())
      this.props.onChange(tinyContent, offsets)
    })

    this.editor.on('click', e => {
      this.props.onClick(e.target)
    })
  }

  componentWillReceiveProps(nextProps) {
    if ((nextProps.content !== this.editor.getContent()
      && nextProps.content !== this.props.content)) {
      this.editor.setContent(nextProps.content)
    }
  }

  shouldComponentUpdate(nextProps) {
    return (nextProps.content !== this.editor.getContent()
    && nextProps.content !== this.props.content)
  }

  componentWillUnmount() {
    this.editor.destroy()
    this.editor = null
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
        ref={(el) => this.textarea = el}
        className="form-control"
        defaultValue={this.props.content}
      />
    )
  }
}

Editor.propTypes = {
  id: T.string.isRequired,
  content: T.string.isRequired,
  onChange: T.func.isRequired,
  onSelect: T.func,
  onClick: T.func,
  disabled: T.bool,

  showModal: T.func.isRequired
}

const Tinymce = withModal(Editor)

export {
  Tinymce
}
