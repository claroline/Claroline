import React, {PureComponent} from 'react'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {copy} from '#/main/app/clipboard'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

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
        [`input-group-${this.props.size}`]: !!this.props.size
      })}>
        <input
          id={this.props.id}
          type="text"
          value={this.props.value || ''}
          disabled={this.props.disabled}
          placeholder={this.props.placeholder}
          autoComplete={this.props.autoComplete}
          onChange={this.onChange}
        />

        <span className="input-group-btn">
          <Button
            id={`clipboard-${this.props.id}`}
            type={CALLBACK_BUTTON}
            tooltip="left"
            label={trans('clipboard_copy')}
            className="btn"
            icon="fa fa-fw fa-clipboard"
            callback={this.copyToClipboard}
          />
        </span>
      </div>
    )
  }
}

implementPropTypes(UrlInput, FormFieldTypes, {
  value: T.string
}, {
  value: ''
})

export {
  UrlInput
}
