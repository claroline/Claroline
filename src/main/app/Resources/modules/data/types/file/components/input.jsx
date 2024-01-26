import React, {Component} from 'react'
import {connect} from 'react-redux'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {param} from '#/main/app/config'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {actions} from '#/main/app/api/store'

import {FileDropContext} from '#/main/app/overlays/dnd/file-drop-context'
import {getType} from '#/main/app/data/types/file/utils'
import {FileThumbnail} from '#/main/app/data/types/file/components/thumbnail'

class FileComponent extends Component {
  constructor(props, context) {
    super(props, context)

    this.onFileSelect = this.onFileSelect.bind(this)
  }

  onFileSelect() {
    if (!isEmpty(this.input.files)) {
      this.onChange(this.input.files)
    }
  }

  onChange(files) {
    if (!this.props.multiple) {
      const file = files[0]
      if (this.props.autoUpload) {
        this.props.uploadFile(file, this.props.uploadUrl, this.props.onChange)
      } else {
        this.props.onChange(file)
      }
    } else {
      // Only manages multiple files if autoUpload is false
      if (this.props.autoUpload) {
        this.props.uploadFile(files[0], this.props.uploadUrl, this.props.onChange)
      } else {
        this.props.onChange(files)
      }
    }
  }

  render() {
    return (
      <div className={classes('file-control', this.props.className)}>
        <input
          id={this.props.id}
          type="file"
          className={classes('form-control', this.props.className, {
            [`form-control-${this.props.size}`]: !!this.props.size
          })}
          accept={this.props.types.join(',')}
          multiple={this.props.multiple}
          ref={input => this.input = input}
          onChange={this.onFileSelect}
          disabled={this.props.disabled}
        />

        {param('uploadMaxFilesize') &&
          <div className="form-text">{trans('max_filesize', {size: param('uploadMaxFilesize')})}</div>
        }

        {this.props.value &&
          <div className="file-thumbnails">
            <FileThumbnail
              type={getType(this.props.value.mimeType || this.props.value.type)}
              data={this.props.value}
              canEdit={false}
              canExpand={false}
              canDownload={false}
              canDelete={!this.props.disabled}
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
      </div>
    )
  }
}

// register to the FileDropContext to know when a file enters the window
FileComponent.contextType = FileDropContext

implementPropTypes(FileComponent, DataInputTypes, {
  // more precise value type
  value: T.oneOfType([
    T.array,
    T.shape({
      id: T.number,
      mimeType: T.string.isRequired,
      url: T.string.isRequired
    })
  ]),

  // custom props
  types: T.arrayOf(T.string),

  multiple: T.bool,
  min: T.number,
  max: T.number,

  uploadUrl: T.oneOfType([T.string, T.array]),
  autoUpload: T.bool,

  // async method for autoUpload
  uploadFile: T.func.isRequired,
  deleteFile: T.func.isRequired
}, {
  types: [],
  multiple: false,
  autoUpload: true,
  uploadUrl: ['apiv2_file_upload']
})

//this is not pretty
const FileInput = connect(
  null,
  dispatch => ({
    uploadFile(file, url, onSuccess) {
      dispatch(actions.uploadFile(file, url)).then((response) => {
        onSuccess(Array.isArray(response) ? response[0] : response)
      })
    },
    deleteFile(file, callback) {
      dispatch(actions.deleteFile(file, callback))
    }
  })
)(FileComponent)

export {
  FileInput
}
