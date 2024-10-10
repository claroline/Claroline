import React, {PureComponent} from 'react'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {copy} from '#/main/app/clipboard'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'

import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {getValidationClassName} from '#/main/app/content/form/validator'

class UrlInput extends PureComponent {
  constructor(props) {
    super(props)

    this.onChange = this.onChange.bind(this)
    this.copyToClipboard = this.copyToClipboard.bind(this)
  }

  onChange(e) {
    this.props.onChange(e.target.value)
  }

  copyToClipboard() {
    copy(this.props.value)
  }

  render() {
    return (
      <div className={classes('input-group', this.props.className, {
        [`input-group-${this.props.size}`]: !!this.props.size,
        'has-validation': !isEmpty(this.props.error)
      })} role="presentation">
        <input
          id={this.props.id}
          type="text"
          className={classes('form-control', getValidationClassName(this.props.error, this.props.validating))}
          value={this.props.value || ''}
          disabled={this.props.disabled}
          placeholder={this.props.placeholder}
          autoFocus={this.props.autoFocus}
          autoComplete={this.props.autoComplete}
          onChange={this.onChange}
          aria-required={this.props.required}
          aria-invalid={!isEmpty(this.props.error)}
        />

        <Button
          id={`clipboard-${this.props.id}`}
          type={CALLBACK_BUTTON}
          tooltip="left"
          label={trans('clipboard_copy')}
          className="btn btn-body"
          icon="fa fa-fw fa-clipboard"
          callback={this.copyToClipboard}
        />
      </div>
    )
  }
}

implementPropTypes(UrlInput, DataInputTypes, {
  value: T.string
}, {
  value: ''
})

export {
  UrlInput
}
