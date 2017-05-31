import React from 'react'
import {PropTypes as T} from 'prop-types'
import DatePicker from 'react-datepicker'
import moment from 'moment'
import 'react-datepicker/dist/react-datepicker.css'

import {getLocale} from '#/main/core/locale'

const CustomDatePicker = ({id, name, value, onChange}) =>
  <DatePicker
    id={id || null}
    name={name}
    className="form-control"
    selected={value ? moment.utc(value) : null}
    minDate={moment.utc()}
    locale={getLocale()}
    onChange={date => onChange(moment.utc(date).format())}
    onBlur={() => {}}
  />

CustomDatePicker.propTypes = {
  id: T.string,
  name: T.string.isRequired,
  onChange: T.func.isRequired,
  value: T.string
}

export {CustomDatePicker as DatePicker}
