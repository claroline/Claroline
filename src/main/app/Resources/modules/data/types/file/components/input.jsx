import React, {Component} from 'react'
import {connect} from 'react-redux'
import classes from 'classnames'
import cloneDeep from 'lodash/cloneDeep'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {param} from '#/main/app/config'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {actions} from '#/main/app/api/store'

import {FileDropContext} from '#/main/app/overlays/dnd/file-drop-context'
import {FileThumbnail} from '#/main/app/data/types/file/components/thumbnail'

class FileComponent extends Component {
  constructor(props, context) {
    super(props, context)

    this.onFileSelect = this.onFileSelect.bind(this)
  }

  onFileSelect() {
    if (!isEmpty(this.input.files)) {
      const uploaded = []
      for (let file of this.input.files) {
        uploaded.push(file)
      }

      this.onChange(uploaded)
    }
  }

  onChange(files) {
    if (!this.props.multiple) {
      const file = files[0]
      if (this.props.autoUpload) {
        this.props.uploadFile(file, this.props.uploadUrl).then(this.props.onChange)
      } else {
        this.props.onChange(file)
      }
    } else {
      // Only manages multiple files if autoUpload is false
      if (this.props.autoUpload) {
        Promise.all(
          files.map(file => this.props.uploadFile(file, this.props.uploadUrl))
        ).then(this.props.onChange)
      } else {
        this.props.onChange(files)
      }
    }
  }

  render() {
    let value
    if (!isEmpty(this.props.value)) {
      value = Array.isArray(this.props.value) ? this.props.value : [this.props.value]
    }

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

        {!isEmpty(value) &&
          <>
            {value.map((file, index) =>
              <FileThumbnail
                key={file.name}
                className="mt-1"
                file={file}
                disabled={this.props.disabled}
                delete={() => {
                  let newValue = null
                  if (this.props.multiple) {
                    newValue = cloneDeep(this.props.value)
                    newValue.splice(index, 1)
                  }

                  if (file.id) {
                    this.props.deleteFile(file.id).then(() => this.props.onChange(newValue))
                  } else {
                    this.props.onChange(newValue)
                  }
                }}
              />
            )}
          </>
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
      name: T.String,
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

const FileInput = connect(
  null,
  dispatch => ({
    uploadFile(file, url) {
      return dispatch(actions.uploadFile(file, url)).then((response) => Array.isArray(response) ? response[0] : response)
    },
    deleteFile(file, callback) {
      return dispatch(actions.deleteFile(file, callback))
    }
  })
)(FileComponent)

export {
  FileInput
}
