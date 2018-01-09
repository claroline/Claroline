import React, {Component} from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

//this is not pretty
import {connect} from 'react-redux'
import {actions} from '#/main/core/data/form/actions'

class FileInput extends Component
{
  render() {
    return (
      <input
        id={this.props.id}
        type="file"
        className="form-control"
        accept={`${this.props.types.join(',')}`}
        ref={input => this.input = input}
        onChange={() => {
          if (this.input.files[0]) {
            const file = this.input.files[0]
            //this.props.value = 'publicFile'
            if (this.props.autoUpload) {
              this.props.uploadFile(file, this.props.uploadUrl, this.props.onChange)
            }
          }
        }}
      />
    )
  }
}

implementPropTypes(FileInput, FormFieldTypes, {
  value: T.object,
  types: T.arrayOf(T.string).isRequired,
  max: T.number.isRequired,
  autoUpload: T.bool.isRequired,
  uploadUrl: T.array,
  uploadFile: T.func.isRequired
}, {
  types: [],
  max: 1,
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
    }
  })
)(FileInput)

export {
  File
}
