import React, {PureComponent} from 'react'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {url} from '#/main/app/api'
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

    // the full public file object
    this.state = {
      loaded: false,
      file: null
    }
  }

  componentDidMount() {
    this.load()
  }

  componentDidUpdate(prevProps) {
    if (prevProps.value !== this.props.value) {
      this.load()
    }
  }

  load() {
    if (!this.props.value) {
      return
    }

    fetch(url(['apiv2_public_file_find', {filters: {url: this.props.value}}]), {
      method: 'GET' ,
      headers: new Headers({
        'Content-Type': 'application/json; charset=utf-8',
        // next header is required for symfony to recognize our requests as XMLHttpRequest
        // there is no spec about possible values, but this is the one expected by symfony
        // @see Symfony\Component\HttpFoundation\Request::isXmlHttpRequest
        'X-Requested-With': 'XMLHttpRequest'
      }),
      credentials: 'include'
    })
      .then(response => response.json(),
        () => this.setState({loaded: true, file: null}))
      .then(
        (data) => this.setState({loaded: true, file: data})
      )
  }

  onChange() {
    if (this.input.files[0]) {
      this.props.uploadFile(this.input.files[0], this.props.uploadUrl, this.props.onChange, this.props.onError)
    }
  }

  onDelete() {
    if (this.state.file) {
      this.props.deleteFile(this.state.file.id, this.props.onChange)
    } else {
      // this permits to empty missing file
      this.props.onChange(null)
    }
  }

  render() {
    return (
      <fieldset className={this.props.className}>
        {(!this.props.value || !this.state.file) &&
          <input
            id={this.props.id}
            type="file"
            className="form-control"
            accept="image"
            ref={input => this.input = input}
            onChange={this.onChange}
          />
        }

        {this.props.value &&
          <div className="img-preview">
            <img
              className="img-thumbnail"
              src={asset(this.props.value)}
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
              disabled={!this.state.loaded}
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
  value: T.string, // the url of the image
  size: T.arrayOf(T.number),
  deleteFile: T.func.isRequired,
  uploadUrl: T.array.isRequired,
  uploadFile: T.func.isRequired
}, {
  size: [200, 200],
  uploadUrl: ['apiv2_image_upload']
})

const ImageInput = connect(
  null,
  dispatch => ({
    uploadFile(file, url, onSuccess, onError) {
      dispatch(actions.uploadFile(file, url)).then(
        (response) => onSuccess(Array.isArray(response) ? response[0].url : response.url),
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
