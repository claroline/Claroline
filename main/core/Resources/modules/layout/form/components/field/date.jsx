import React, {Component} from 'react'
import DatePicker from 'react-datepicker'
import classes from 'classnames'
import moment from 'moment'
import 'react-datepicker/dist/react-datepicker.css'

import {getLocale} from '#/main/core/intl/locale'
import {getFormat} from '#/main/core/scaffolding/date'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

// todo : date input should only return date part (no time).
//        But if I remove it now, it will break deserializers which expect time to be present

// class is required because we use refs inside the component
class Date extends Component {
  render() {
    return (
      <span>
        {this.props.onlyButton &&
          <button
            className="btn btn-sm btn-filter"
            onClick={e => {
              this._calendar.setOpen(true)

              // stop propagation
              // this is mostly required when input is used in list search (without it it submit the filter)
              e.preventDefault()
              e.stopPropagation()
            }}
          >
            <span className="fa fa-fw fa-calendar" />
          </button>
        }
        <DatePicker
          {...this.props}
          name={this.props.id}
          className={classes('form-control', this.props.onlyButton && 'input-hide')}
          locale={getLocale()}
          dateFormat={getFormat(false)}
          ref={(c) => this._calendar = c}
          selected={this.props.value ? moment(this.props.value) : ''}
          onChange={date => this.props.onChange(moment(date).format('YYYY-MM-DD\THH:mm:ss'))}
        />
      </span>
    )
  }
}

implementPropTypes(Date, FormFieldTypes, {
  value: T.string,
  // custom props
  minDate: T.object,
  maxDate: T.object,
  onlyButton: T.bool
}, {
  value: '',
  minDate: moment.utc(),
  onlyButton: false
})

export {
  Date
}
