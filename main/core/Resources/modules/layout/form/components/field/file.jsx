import React, {Component} from 'react'
import {connect} from 'react-redux'
import cloneDeep from 'lodash/cloneDeep'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {actions} from '#/main/core/api/actions'

import {FileThumbnail} from '#/main/core/layout/form/components/field/file-thumbnail.jsx'

class FileComponent extends Component {
  constructor(props) {
    super(props)

    let value = []
    if (props.value) {
      value = props.multiple ? props.value : [props.value]
    }

    this.state = {
      files: value
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

        this.setState(
          {files: files},
          () => {
            if (this.props.autoUpload) {
              this.props.uploadFile(file, this.props.uploadUrl, () => this.onChange())
            } else {
              this.onChange()
            }
          }
        )
      }
    }
  }

  removeFile(idx) {
    const files = cloneDeep(this.state.files)
    const deletedFile = files.splice(idx, 1)

    this.setState(
      {files: files},
      () => {
        if (this.props.autoUpload && deletedFile.id) {
          this.props.deleteFile(deletedFile.id, () => this.onChange())
        } else {
          this.onChange()
        }
      })
  }

  onChange() {
    if (this.props.multiple) {
      this.props.onChange(this.state.files)
    } else {
      this.props.onChange(this.state.files[0] || null)
    }
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
          id={this.props.id}
          type="file"
          className="form-control"
          accept={this.props.types.join(',')}
          ref={input => this.input = input}
          onChange={() => {
            if (this.input.files[0]) {
              this.addFile(this.input.files[0])
            }
          }}
        />

        {0 !== this.state.files.length &&
          <div className="file-thumbnails">
            {this.state.files.map((file, index) =>
              <FileThumbnail
                key={index}
                type={!file.mimeType ? 'file' : this.getFileType(file.mimeType)}
                data={file}
                canEdit={false}
                canExpand={false}
                canDownload={false}
                handleDelete={() => this.removeFile(index)}
              />
            )}
          </div>
        }
      </fieldset>
    )
  }
}

implementPropTypes(FileComponent, FormFieldTypes, {
  // more precise value type
  value: T.oneOfType([T.array, T.object]),
  // custom props
  types: T.arrayOf(T.string),

  multiple: T.bool,
  min: T.number,
  max: T.number,

  autoUpload: T.bool,

  // async method for autoUpload
  uploadFile: T.func.isRequired,
  deleteFile: T.func.isRequired
}, {
  types: [],

  multiple: false,

  autoUpload: true,
  onChange: () => {},
  uploadUrl: ['apiv2_file_upload']
})

//this is not pretty
const File = connect(
  null,
  dispatch => ({
    uploadFile(file, url, callback) {
      dispatch(actions.uploadFile(file, url, callback))
    },
    deleteFile(file, url, callback) {
      dispatch(actions.deleteFile(file, url, callback))
    }
  })
)(FileComponent)

export {
  File
}
