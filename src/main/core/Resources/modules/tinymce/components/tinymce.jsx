import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {withModal} from '#/main/app/overlays/modal/withModal'
import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'

import {tinymce} from '#/main/core/tinymce'
import {config} from '#/main/core/tinymce/config'

import {getOffsets} from '#/main/core/scaffolding/text/selection'

// todo : move in app
// todo : correct management of container
// todo : allow to add custom CSS
class Editor extends Component {
  constructor(props) {
    super(props)

    this.editor = null
  }

  componentDidMount() {
    config.then((loadedConfig) => {
      tinymce.init(
        Object.assign({}, loadedConfig, {
          target: this.textarea,
          //ui_container: `#${this.props.id}-container`,

          // give access to the show modal action to tinymce plugins
          // it's not really aesthetic but there is no other way
          showModal: this.props.showModal,
          // get the current workspace for the file upload and resource explorer plugins
          workspace: this.props.workspace
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
    })
  }

  componentDidUpdate(prevProps) {
    if ((this.props.content !== this.editor.getContent() && this.props.content !== prevProps.content)) {
      this.editor.setContent(this.props.content)
    }
  }

  shouldComponentUpdate(nextProps) {
    return this.editor
      && nextProps.content !== this.editor.getContent()
      && nextProps.content !== this.props.content
  }

  componentWillUnmount() {
    if (this.editor) {
      this.editor.destroy()
      this.editor = null
    }
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
        className={classes('form-control', {
          [`input-${this.props.size}`]: !!this.props.size,
          disabled: this.props.disabled
        })}
        defaultValue={this.props.content}
        placeholder={this.props.placeholder}
      />
    )
  }
}

Editor.propTypes = {
  id: T.string.isRequired,
  size: T.oneOf(['sm', 'lg']),
  content: T.string.isRequired,
  onChange: T.func.isRequired,
  onSelect: T.func,
  onClick: T.func,
  disabled: T.bool,
  placeholder: T.string,

  showModal: T.func.isRequired,
  workspace: T.shape(
    WorkspaceTypes.propTypes
  )
}

const Tinymce = withModal(Editor)

export {
  Tinymce
}
