import React, {Component} from 'react'
import {connect} from 'react-redux'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {actions} from '#/main/app/api/store'

import {FileDropContext} from '#/main/app/overlays/dnd/file-drop-context'
import {getType} from '#/main/app/data/types/file/utils'
import {FileThumbnail} from '#/main/app/data/types/file/components/thumbnail'

// todo handle unzippable

function getEventFiles(e) {
  let files = []
  if (e.dataTransfer.items) {
    // Use DataTransferItemList interface to access the file(s)
    for (let i = 0; i < e.dataTransfer.items.length; i++) {
      // If dropped items aren't files, reject them
      if (e.dataTransfer.items[i].kind === 'file') {
        files.push(
          e.dataTransfer.items[i].getAsFile()
        )
      }
    }
  } else {
    // Use DataTransfer interface to access the file(s)
    files = e.dataTransfer.files
  }

  return files
}

class FileComponent extends Component {
  constructor(props, context) {
    super(props, context)

    this.onFileDrop = this.onFileDrop.bind(this)
    this.onFileSelect = this.onFileSelect.bind(this)
  }

  onFileDrop(e) {
    e.preventDefault() // prevent file from being opened

    // grab files from event to upload them
    const files = getEventFiles(e)
    if (!isEmpty(files)) {
      // upload dropped files
      this.onChange(files)
    }
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
      <div
        className={classes('file-control', this.props.className, {
          'highlight': this.context
        })}
        onDrop={this.onFileDrop}
      >
        {this.context &&
          <div className="files-drop-placeholder">
            Déposez vos fichiers ici
          </div>
        }

        <input
          id={this.props.id}
          type="file"
          className="form-control"
          disabled={this.props.disabled}
          multiple={this.props.multiple}
          accept={this.props.types.join(',')}
          ref={input => this.input = input}
          onChange={this.onFileSelect}
        />

        <button
          type="button"
          className="files-drop-container"
          onClick={() => this.input.click()}
        >
          <div className="files-drop">
            <span className="files-drop-icon fa fa-file-upload" />
            <div className="files-drop-label">
              Choisir un fichier
              <span className="files-drop-info">Vous pouvez aussi glisser/déposer un fichier ici</span>
            </div>
          </div>
        </button>

        {this.props.value &&
          <div className="file-thumbnails">
            <FileThumbnail
              type={getType(this.props.value.mimeType || this.props.value.type)}
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
