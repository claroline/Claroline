import React, {Component} from 'react'
import {connect} from 'react-redux'
import has from 'lodash/has'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {actions} from '#/main/app/api/store'

import {FileThumbnail} from '#/main/app/data/types/file/components/thumbnail'

class FileComponent extends Component {
  constructor(props) {
    super(props)

    this.state = {
      unzip: false
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
          disabled={this.props.disabled}
          multiple={this.props.multiple}
          accept={this.props.types.join(',')}
          ref={input => this.input = input}
          onChange={() => {
            if (this.input.files[0]) {
              if (!this.props.multiple) {
                const file = this.input.files[0]
                if (this.props.autoUpload) {
                  this.props.uploadFile(file, this.props.uploadUrl, this.props.onChange)
                } else {
                  this.props.onChange(file)
                }
              } else {
                // Only manages multiple files if autoUpload is false
                if (this.props.autoUpload) {
                  this.props.uploadFile(this.input.files[0], this.props.uploadUrl, this.props.onChange)
                } else {
                  this.props.onChange(this.input.files)
                }
              }
            }}
          }
        />

        {has(this.props.value, 'mimeType') && has(this.props.value, 'url') &&
          <div className="file-thumbnails">
            <FileThumbnail
              type={this.getFileType(this.props.value.mimeType)}
              data={this.props.value}
              canEdit={false}
              canExpand={false}
              canDownload={false}
              handleDelete={() => {
                if (this.props.value.id) {
                  this.props.deleteFile(this.props.value.id, this.props.onChange)
                } else {
                  this.props.onChange(null)
                }
              }}
            />
          </div>
        }
      </fieldset>
    )
  }
}

implementPropTypes(FileComponent, DataInputTypes, {
  // more precise value type
  value: T.oneOfType([T.array, T.object]),
  // custom props
  types: T.arrayOf(T.string),

  multiple: T.bool,
  min: T.number,
  max: T.number,

  uploadUrl: T.oneOfType([T.string, T.arrayOf(T.string)]),
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
    uploadFile(file, url, onSuccess) {
      dispatch(actions.uploadFile(file, url)).then((response) => onSuccess(Array.isArray(response) ? response[0] : response))
    },
    deleteFile(file, callback) {
      dispatch(actions.deleteFile(file, callback))
    }
  })
)(FileComponent)

export {
  File
}
