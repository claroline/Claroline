import React, {Component} from 'react'
import {connect} from 'react-redux'
import has from 'lodash/has'

import {asset} from '#/main/core/scaffolding/asset'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {actions} from '#/main/core/api/actions'
import {FileThumbnail} from '#/main/core/layout/form/components/field/file-thumbnail.jsx'

// todo : merge with file type

class Image extends Component {
  constructor(props) {
    super(props)
  }

  render() {
    return (
      <fieldset>
        {!has(this.props.value, 'id') &&
          <input
            id={this.props.id}
            type="file"
            className="form-control"
            accept="image"
            ref={input => this.input = input}
            onChange={() => {
              if (this.input.files[0]) {
                const file = this.input.files[0]

                if (this.props.autoUpload) {
                  this.props.uploadFile(file, this.props.uploadUrl, this.props.onChange)
                }
              }
            }
            }
          />
        }

        {has(this.props.value, 'id') &&
          <img
            src={asset(this.props.value.url)}
            className="img-thumbnail"
            style={{
              maxWidth: this.props.size[0] + 'px',
              maxHeight: this.props.size[1] + 'px'
            }}
          />
        }

      </fieldset>
    )
  }
}

// this.props.deleteFile(this.props.value.id, this.props.onChange)

implementPropTypes(Image, FormFieldTypes, {
  value: T.object,
  size: T.arrayOf(T.number),
  autoUpload: T.bool.isRequired,
  deleteFile: T.func.isRequired,
  uploadUrl: T.array.isRequired,
  uploadFile: T.func.isRequired
}, {
  size: [200, 200],
  autoUpload: true,
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
