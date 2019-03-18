import React, {PureComponent} from 'react'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

class TimeInput extends PureComponent {
  constructor(props) {
    super(props)

    this.onChange = this.onChange.bind(this)
  }

  onChange(e) {
    this.props.onChange(e.target.value)
  }

  render() {
    const commonProps = {
      id: this.props.id,
      className: classes('form-control', {
        [`input-${this.props.size}`]: !!this.props.size
      }),
      value: this.props.value || '',
      disabled: this.props.disabled,
      onChange: this.onChange,
      autoComplete: this.props.autoComplete
    }

    return (
      <div className={classes('input-group', this.props.className, {
        [`input-group-${this.props.size}`]: !!this.props.size
      })}>
        <input
          className="form-control text-right"
          type="number"
          min={0}
          disabled={this.props.disabled}
        />

        <span className="input-group-addon">
          <span className="hidden-xs">{trans('hours')}</span>
          <span className="visible-xs">{trans('hours_short')}</span>
        </span>

        <input
          className="form-control text-right"
          type="number"
          min={0}
          disabled={this.props.disabled}
        />

        <span className="input-group-addon">
          <span className="hidden-xs">{trans('minutes')}</span>
          <span className="visible-xs">{trans('minutes_short')}</span>
        </span>

        <input
          className="form-control text-right"
          type="number"
          min={0}
          disabled={this.props.disabled}
        />

        <span className="input-group-addon">
          <span className="hidden-xs">{trans('seconds')}</span>
          <span className="visible-xs">{trans('seconds_short')}</span>
        </span>
      </div>
    )
  }
}

implementPropTypes(TimeInput, FormFieldTypes, {
  value: T.number,

  min: T.number, // todo : implement
  max: T.number // todo : implement
}, {
  value: ''
})

export {
  TimeInput
}
