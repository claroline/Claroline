import React, {Component, createRef} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'

function getEventFiles(ev) {
  let files = []
  if (ev.dataTransfer.items) {
    // Use DataTransferItemList interface to access the file(s)
    [...ev.dataTransfer.items].forEach((item) => {
      // If dropped items aren't files, reject them
      if ('file' === item.kind) {
        files.push(item.getAsFile())
      }
    })
  } else {
    // Use DataTransfer interface to access the file(s)
    files = [...ev.dataTransfer.files]
  }

  return files
}

class FileDrop extends Component {
  constructor(props) {
    super(props)

    this.state = {
      count: 0
    }

    this.dropTarget = createRef()

    this.onDragStart = this.onDragStart.bind(this)
    this.onDragEnd = this.onDragEnd.bind(this)
    this.onDragOver = this.onDragOver.bind(this)
    this.onDrop = this.onDrop.bind(this)
  }

  componentDidMount() {
    document.addEventListener('dragenter', this.onDragStart, false)
    document.addEventListener('dragleave', this.onDragEnd, false)
    document.addEventListener('dragover', this.onDragOver, false)
    document.addEventListener('drop', this.onDrop, false)
  }

  componentWillUnmount() {
    document.removeEventListener('dragenter', this.onDragStart)
    document.removeEventListener('dragleave', this.onDragEnd)
    document.removeEventListener('dragover', this.onDragOver)
    document.removeEventListener('drop', this.onDrop)
  }

  onDragStart(e) {
    if (!this.props.disabled && !isEmpty(getEventFiles(e))) {
      e.stopPropagation()
      e.preventDefault()

      this.setState((state) => ({count: state.count + 1}))
    }
  }

  onDragOver(e) {
    if (!this.props.disabled && !isEmpty(getEventFiles(e))) {
      e.stopPropagation()
      e.preventDefault()
    }
  }

  onDragEnd(e) {
    if (!this.props.disabled && !isEmpty(getEventFiles(e))) {
      e.stopPropagation()
      e.preventDefault()

      this.setState((state) => ({count: state.count - 1}))
    }
  }

  onDrop(e) {
    const files = getEventFiles(e)
    if (!this.props.disabled && !isEmpty(files)) {
      e.stopPropagation()
      e.preventDefault()

      if (this.dropTarget.current === e.target || this.dropTarget.current.contains(e.target)) {
        this.props.onDrop(files)
      }

      this.setState((state) => ({count: state.count - 1}))
    }
  }

  render() {
    return (
      <div
        className={classes(this.props.className, 'file-dropzone', {
          'file-dropzone-highlight': 0 < this.state.count && !this.props.disabled
        }, this.props.size && `file-dropzone-${this.props.size}`)}
      >
        {!this.props.disabled &&
          <div className={classes('file-dropzone-target')} ref={this.dropTarget}>
            <div className="file-dropzone-help sticky-bottom">
              <span className="file-dropzone-help-icon">
                <span className="fa fa-file-arrow-up" />
                <span className="fa fa-file" />
              </span>
              <span className="file-dropzone-help-message">
                {this.props.help}
              </span>
            </div>
          </div>
        }

        {this.props.children}
      </div>
    )
  }
}

FileDrop.propTypes = {
  className: T.string,
  disabled: T.bool,
  // The list of accepted file mimeTypes as defined for <input type="file" />
  accept: T.arrayOf(T.string),
  onDrop: T.func.isRequired,
  children: T.node.isRequired,
  help: T.string,
  size: T.oneOf(['sm', 'lg'])
}

FileDrop.defaultProps = {
  help: trans('file_drop_help'),
  disabled: false
}

export {
  FileDrop
}
