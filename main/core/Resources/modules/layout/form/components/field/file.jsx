import React, {Component} from 'react'
import {connect} from 'react-redux'
import has from 'lodash/has'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {actions} from '#/main/core/api/actions'

import {FileThumbnail} from '#/main/core/layout/form/components/field/file-thumbnail.jsx'

class FileComponent extends Component {

  constructor(props) {
    super(props)
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
              const file = this.input.files[0]

              if (this.props.autoUpload) {
                this.props.uploadFile(file, this.props.uploadUrl, this.props.onChange)
              }
            }}
          }
        />

        {has(this.props.value, 'id') &&
          <div className="file-thumbnails">
            <FileThumbnail
              type={this.getFileType(this.props.value.mimeType)}
              data={this.props.value}
              canEdit={false}
              canExpand={false}
              canDownload={false}
              handleDelete={() => this.props.deleteFile(this.props.value.id, this.props.onChange)}
            />
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

  min: T.number,
  max: T.number,

  autoUpload: T.bool,

  // async method for autoUpload
  uploadFile: T.func.isRequired,
  deleteFile: T.func.isRequired
}, {
  types: [],
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
    deleteFile(file, callback) {
      dispatch(actions.deleteFile(file, callback))
    }
  })
)(FileComponent)

export {
  File
}
