import React, {PureComponent} from 'react'
import {connect} from 'react-redux'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {url} from '#/main/app/api'
import {asset} from '#/main/app/config/asset'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Alert} from '#/main/app/alert/components/alert'

import {actions} from '#/main/app/api/store'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'

class ImageInputComponent extends PureComponent {
  constructor(props) {
    super(props)

    this.onChange = this.onChange.bind(this)
    this.onDelete = this.onDelete.bind(this)

    // the full public file object
    this.state = {
      loaded: false,
      file: null,
      notFound: false,
      error: false
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
      .then(response => {
        if (!response.ok) {
          if (404 === response.status) {
            this.setState({loaded: false, file: null, notFound: true})
          } else {
            this.setState({loaded: false, file: null, error: true})
          }

          return Promise.reject(response)
        }

        return response.json()
      })
      .then((data) => {
        this.setState({loaded: true, file: data, notFound: false, error: false})
      })

  }

  onChange() {
    if (this.input.files[0]) {
      this.props.uploadFile(this.input.files[0], this.props.uploadUrl, this.props.onChange, this.props.onError)
    }
  }

  onDelete() {
    // the file will be automatically deleted by the API if no longer used.
    this.props.onChange(null)
  }

  render() {
    return (
      <fieldset className={this.props.className}>
        {this.state.notFound && !this.state.file &&
          <Alert type="warning" className="mb-3">
            {trans('image_not_found')}

            <div className="btn-toolbar mt-3 justify-content-end">
              <Button
                className="btn btn-warning"
                size="sm"
                type={CALLBACK_BUTTON}
                callback={() => this.input.click()}
                label={trans('replace_image', {}, 'actions')}
                disabled={this.props.disabled}
              />
            </div>
          </Alert>
        }

        {this.state.error && !this.state.file &&
          <Alert type="danger" className="mb-3">
            {trans('image_error')}
          </Alert>
        }

        {(!this.props.value || !this.state.file) &&
          <input
            id={this.props.id}
            style={this.state.notFound ? {display: 'none'} : undefined}
            type="file"
            className={classes('form-control', this.props.className, {
              [`form-control-${this.props.size}`]: !!this.props.size
            })}
            accept="image"
            ref={input => this.input = input}
            onChange={this.onChange}
            disabled={this.props.disabled}
          />
        }

        {this.props.value && this.state.loaded &&
          <div className="img-preview">
            <img
              className="img-thumbnail"
              src={asset(this.props.value)}
            />

            <Button
              id={`${this.props.id}-delete`}
              type={CALLBACK_BUTTON}
              variant="btn"
              size="sm"
              icon="fa fa-fw fa-trash"
              label={trans('delete', {}, 'actions')}
              tooltip="left"
              disabled={this.props.disabled}
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
    }
  })
)(ImageInputComponent)

export {
  ImageInput
}
