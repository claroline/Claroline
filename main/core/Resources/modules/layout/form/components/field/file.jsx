import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import cloneDeep from 'lodash/cloneDeep'
import {FileThumbnail} from '#/main/core/layout/form/components/field/file-thumbnail.jsx'

export class File extends Component {
  constructor(props) {
    super(props)
    this.state = {
      files: props.value || []
    }
  }

  isTypeAllowed(type) {
    let isAllowed = this.props.types.length === 0

    if (!isAllowed) {
      const regex = new RegExp(type, 'gi')
      this.props.types.forEach(t => {
        if (t.match(regex)) {
          isAllowed = true
        }
      })
    }

    return isAllowed
  }

  addFile(file) {
    if (file && (!this.props.max || this.state.files.length < this.props.max)) {
      const type = this.getFileType(file.type)

      if (this.isTypeAllowed(type)) {
        const files = cloneDeep(this.state.files)
        files.push(file)
        this.setState({files: files}, () => this.props.onChange(this.state.files))
      }
    }
  }

  removeFile(idx) {
    const files = cloneDeep(this.state.files)
    files.splice(idx, 1)
    this.setState({files: files}, () => this.props.onChange(this.state.files))
  }


  getFileType(mimeType) {
    const typeParts = mimeType.split('/')
    let type = 'file'

    if (typeParts[0] && ['image', 'audio', 'video'].indexOf(typeParts[0]) > -1) {
      type = typeParts[0]
    } else if (typeParts[1]) {
      type = typeParts[1]
    }

    return type
  }

  render() {
    return (
      <fieldset>
        <input
          id={this.props.controlId}
          type="file"
          className="form-control"
          accept={`${this.props.types.join(',')}`}
          ref={input => this.input = input}
          onChange={() => this.addFile(this.input.files[0])}
        />
        <div className="file-thumbnails">
          {this.state.files.map((f, idx) =>
            <FileThumbnail
              key={`file-thumbnail-${idx}`}
              type={!f.mimeType ? 'file' : this.getFileType(f.mimeType)}
              data={f}
              canEdit={false}
              canExpand={false}
              canDownload={false}
              handleDelete={() => this.removeFile(idx)}
            />
          )}
        </div>
      </fieldset>
    )
  }
}

File.propTypes = {
  controlId: T.string.isRequired,
  value: T.array,
  disabled: T.bool.isRequired,
  types: T.arrayOf(T.string).isRequired,
  max: T.number.isRequired,
  onChange: T.func.isRequired
}

File.defaultProps = {
  disabled: false,
  types: [],
  max: 1,
  onChange: () => {}
}
