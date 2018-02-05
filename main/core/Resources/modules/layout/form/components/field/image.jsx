import React, {Component} from 'react'
import {connect} from 'react-redux'
import has from 'lodash/has'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {actions} from '#/main/core/api/actions'
import {FileThumbnail} from '#/main/core/layout/form/components/field/file-thumbnail.jsx'

// todo : merge with file type

class Image extends Component {
  constructor(props) {
    super(props)
  }

  isImage(mimeType) {
    return mimeType.split('/')[0] === 'image'
  }

  onUpload(data) {
    this.props.onChange(data)
  }

  onDelete(data) {
    this.props.onDelete(data)
  }

  render() {
    return (
      <fieldset>
        <input
          id={this.props.id}
          type="file"
          className="form-control"
          accept="image"
          ref={input => this.input = input}
          onChange={() => {
            if (this.input.files[0]) {
              const file = this.input.files[0]
              //this.props.value = 'publicFile'
              if (this.props.autoUpload) {
                this.props.uploadFile(file, this.props.uploadUrl, this.onUpload.bind(this))
              }
            }}
          }
        />

        {has(this.props.value, 'id') &&
          <div className="file-thumbnail">
            <FileThumbnail
              data={this.props.value}
              type="image"
              canEdit={false}
              canExpand={false}
              canDownload={false}
              canDelete={true}
              handleDelete={() => this.props.deleteFile(
                this.state.file.id,
                this.onDelete.bind(this)
              )}
            />
          </div>
        }
      </fieldset>
    )
  }
}

implementPropTypes(Image, FormFieldTypes, {
  value: T.object,
  autoUpload: T.bool.isRequired,
  onDelete: T.func,
  deleteFile: T.func.isRequired,
  uploadUrl: T.array.isRequired,
  uploadFile: T.func.isRequired
}, {
  autoUpload: true,
  onDelete: () => {},
  uploadUrl: ['apiv2_file_upload']
})

//this is not pretty
const ConnectedImage = connect(
  () => ({}),
  dispatch => ({
    uploadFile(file, url, callback) {
      dispatch(actions.uploadFile(file, url, callback))
    },
    deleteFile(file, callback) {
      dispatch(actions.deleteFile(file, callback))
    }
  })
)(Image)

export {
  ConnectedImage as Image
}
