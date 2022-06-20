import React, {PureComponent} from 'react'
import {connect} from 'react-redux'
import has from 'lodash/has'

import {trans} from '#/main/app/intl/translation'
import {asset} from '#/main/app/config/asset'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {actions} from '#/main/app/api/store'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

class ImageInputComponent extends PureComponent {
  constructor(props) {
    super(props)

    this.onChange = this.onChange.bind(this)
    this.onDelete = this.onDelete.bind(this)
  }

  onChange() {
    if (this.input.files[0]) {
      this.props.uploadFile(this.input.files[0], this.props.uploadUrl, this.props.onChange, this.props.onError)
    }
  }

  onDelete() {
    this.props.deleteFile(this.props.value.id, this.props.onChange)
  }

  render() {
    return (
      <fieldset className={this.props.className}>
        {!has(this.props.value, 'id') &&
          <input
            id={this.props.id}
            type="file"
            className="form-control"
            accept="image"
            ref={input => this.input = input}
            onChange={this.onChange}
          />
        }

        {has(this.props.value, 'id') &&
          <div className="img-preview">
            <img
              className="img-thumbnail"
              src={asset(this.props.value.url)}
              style={{
                maxWidth: this.props.size[0] + 'px',
                maxHeight: this.props.size[1] + 'px'
              }}
            />

            <Button
              id={`${this.props.id}-delete`}
              type={CALLBACK_BUTTON}
              className="btn"
              icon="fa fa-fw fa-trash-o"
              label={trans('delete', {}, 'actions')}
              tooltip="left"
              callback={this.onDelete}
              dangerous={true}
            />
          </div>
        }
      </fieldset>
    )
  }
}

implementPropTypes(ImageInputComponent, DataInputTypes, {
  value: T.object,
  size: T.arrayOf(T.number),
  deleteFile: T.func.isRequired,
  uploadUrl: T.array.isRequired,
  uploadFile: T.func.isRequired
}, {
  size: [200, 200],
  uploadUrl: ['apiv2_image_upload']
})

// this is not pretty
const ImageInput = connect(
  null,
  dispatch => ({
    uploadFile(file, url, onSuccess, onError) {
      dispatch(actions.uploadFile(file, url)).then(
        (response) => onSuccess(Array.isArray(response) ? response[0] : response),
        () => onError(trans('invalid_image', {}, 'validators'))
      )
    },
    deleteFile(file, callback) {
      dispatch(actions.deleteFile(file, callback))
    }
  })
)(ImageInputComponent)

export {
  ImageInput
}
