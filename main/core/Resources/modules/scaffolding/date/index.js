import moment from 'moment'

import {t} from '#/main/core/translation'

function getFormat(withTime) {
  return t(withTime ? 'date_range.js_format.with_hours':'date_range.js_format')
}

function isValidDate(value) {
  return moment(value).isValid()
}

function localeDate(date, withTime = false) {
  return moment(date).format(getFormat(withTime))
}

function serverDate(displayDate, withTime = false) {
  return moment(displayDate, getFormat(withTime)).toISOString()
}

export {
  isValidDate,
  localeDate,
  serverDate
}
