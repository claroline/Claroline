import React, {PropTypes as T} from 'react'
import DatePicker from 'react-datepicker'
import moment from 'moment'
import 'react-datepicker/dist/react-datepicker.css'

const locale = getLocale()

export const Date = ({id, name, value, onChange}) =>
  <DatePicker
    id={id || null}
    name={name}
    className="form-control"
    selected={value ? moment.utc(value) : null}
    minDate={moment.utc()}
    locale={locale}
    onChange={date => onChange(moment.utc(date).format())}
    onBlur={() => {}}
  />

Date.propTypes = {
  id: T.string,
  name: T.string.isRequired,
  onChange: T.func.isRequired,
  value: T.string
}

// tmp: current way of retrieving locale...
function getLocale() {
  const locale = document.querySelector('#homeLocale')

  if (locale) {
    return locale.innerHTML.trim()
  }

  return 'en'
}
