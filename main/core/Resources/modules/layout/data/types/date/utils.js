import moment from 'moment'

import {t} from '#/main/core/translation'

export function isValidDate(value) {
  return moment(value).isValid()
}

export function localeDate(date) {
  return moment(date).format(t('date_range.js_format'))
}

export function serverDate(displayDate) {
  return moment(displayDate, t('date_range.js_format')).toISOString()
}
