import React, {PureComponent} from 'react'
import {connect} from 'react-redux'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {url} from '#/main/app/api'
import {asset} from '#/main/app/config/asset'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {Button, Toolbar} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Alert} from '#/main/app/components/alert'

import {actions} from '#/main/app/api/store'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {getValidationClassName} from '#/main/app/content/form/validator'

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

    fetch(url(['apiv2_public_file_get', {field: 'url', id: this.props.value}]), {
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
      <div className={this.props.className} role="presentation">
        <input
          id={this.props.id}
          style={this.state.notFound ? {display: 'none'} : undefined}
          type="file"
          className={classes('form-control', getValidationClassName(this.props.error), this.props.className, {
            [`form-control-${this.props.size}`]: !!this.props.size,
            'visually-hidden': this.state.notFound || this.props.value || this.state.file
          })}
          accept="image"
          ref={input => this.input = input}
          onChange={this.onChange}
          disabled={this.props.disabled}
          aria-required={this.props.required}
          aria-invalid={!isEmpty(this.props.error)}
        />

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

        {this.props.value && this.state.loaded &&
          <div className="img-preview d-flex align-items-start gap-2" role="presentation">
            <img
              className="img-thumbnail overflow-hidden"
              src={asset(this.props.value)}
              style={{maxHeight: '12rem'}}
            />
            <Toolbar
              className="d-flex flex-column gap-1"
              tooltip="left"
              disabled={this.props.disabled}
              size="sm"
              buttonName="btn btn-body"
              actions={[
                {
                  name: 'replace',
                  type: CALLBACK_BUTTON,
                  icon: 'fa fa-fw fa-arrow-right-arrow-left',
                  label: trans('replace_image', {}, 'actions'),
                  callback: () => this.input.click()
                }, {
                  name: 'delete',
                  type: CALLBACK_BUTTON,
                  icon: 'fa fa-fw fa-trash',
                  label: trans('remove_image', {}, 'actions'),
                  callback: this.onDelete
                }
              ]}
            />
          </div>
        }
      </div>
    )
  }
}

implementPropTypes(ImageInputComponent, DataInputTypes, {
  value: T.string, // the url of the image
  previewSize: T.arrayOf(T.number),
  uploadUrl: T.array.isRequired,
  uploadFile: T.func.isRequired
}, {
  previewSize: [200, 200],
  uploadUrl: ['apiv2_public_file_image_upload']
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
