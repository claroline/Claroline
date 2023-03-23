/**
 * Intl : Date
 * Manages localization of date and datetime.
 *
 * @todo : clean exposed api
 */

import moment from 'moment'

import {locale} from '#/main/app/intl/locale'
import {trans} from '#/main/app/intl/translation'

// configure moment
// this may be not the better place to do it
moment.locale(locale())

/**
 * Gets the date format expected by the server API.
 *
 * @return {string}
 */
function getApiFormat() {
  return 'YYYY-MM-DD[T]HH:mm:ss'
}

/**
 * Gets the display format of the current user.
 * For now it uses the format of the current locale.
 *
 * @param {boolean} long     - gets the full text date, otherwise gets the short digit format.
 * @param {boolean} withTime - appends time format.
 */
function getDisplayFormat(long = false, withTime = false) {
  let displayFormat
  if (long) {
    displayFormat = moment.localeData().longDateFormat('ll') // Sep 4, 1986
  } else {
    displayFormat = moment.localeData().longDateFormat('L') // 09/04/1986
  }

  if (withTime) {
    // appends time (time format is the same for long and short format)
    displayFormat += ' ' + moment.localeData().longDateFormat('LT')
  }

  return displayFormat
}

function isValidDate(value, format = null) {
  if (format) {
    return moment(value, format, true).isValid()
  } else {
    return moment(value).isValid()
  }
}

/**
 * Converts a date from the displayed format to the API one.
 *
 * @param {string}  displayDate - the display date to convert.
 * @param {boolean} long        - does the display date use the full text format ?
 * @param {boolean} withTime    - has it time ?
 *
 * @return {string} - the date in api format.
 */
function apiDate(displayDate, long = false, withTime = false) {
  let date = moment(displayDate, getDisplayFormat(long, withTime))
  if (!withTime) {
    // reset time part
    date.hours(0).minutes(0).seconds(0)
  } else {
    // This is causing a lost of 2 hours in date field of a form
    date = date.utc()
  }

  return date.format(getApiFormat())
}

/**
 * Gets formatted date from Date object.
 *
 * @param {Date} date
 * @param {boolean} long     - does the display date use the full text format ?
 * @param {boolean} withTime - has it time ?
 *
 * @return {string} formatted date
 */
function dateToDisplayFormat(date, long = false, withTime = false) {
  return moment(date).format(getDisplayFormat(long, withTime))
}

/**
 * Converts a date from the api format to the displayed one.
 *
 * @param {string}  apiDate  - the api date to convert.
 * @param {boolean} long     - does the display date use the full text format ?
 * @param {boolean} withTime - has it time ?
 *
 * @return {string} - the date in display format.
 */
function displayDate(apiDate, long = false, withTime = false) {
  return moment.utc(apiDate).local().format(getDisplayFormat(long, withTime))
}

/**
 * Converts time of a datetime from the api format to the displayed one.
 *
 * @param {string}  apiDate - the api date to convert.
 *
 * @return {string} - the time in display format.
 */
function displayTime(apiDate) {
  return moment.utc(apiDate).local().format('LT')
}

function displayDateRange(start, end) {
  let date = displayDate(start, true)
  if (end) {
    if (moment(start).isSame(end, 'day')) {
      date += ' | ' + trans('time_range', {
        start: displayTime(start),
        end: displayTime(end)
      })
    } else {
      date = trans('date_range', {
        start: displayDate(start, true),
        end: displayDate(end, true)
      })
    }
  }

  return date
}

function displayDuration(seconds, long = false) {
  const time = moment.duration({seconds: seconds})

  if (time.years() > 0) {
    return (time.years() + time.months() / 12).toFixed(1) + ' ' + trans(long ? 'years' : 'years_short')
  }
  if (time.months() > 0) {
    return (time.months() + time.days() / 30).toFixed(1) + ' ' + trans(long ? 'months' : 'months_short')
  }
  if (time.days() > 0) {
    return (time.days() + time.hours() / 24).toFixed(1) + ' ' + trans(long ? 'days' : 'days_short')
  }
  if (time.hours() > 0) {
    return (time.hours() + time.minutes() / 60).toFixed(1) + ' ' + trans(long ? 'hours' : 'hours_short')
  }
  if (time.minutes() > 0) {
    return (time.minutes() + time.seconds() / 60).toFixed(1) + ' ' + trans(long ? 'minutes' : 'minutes_short')
  }

  return time.seconds().toFixed(1) + ' ' + trans(long ? 'seconds' : 'seconds_short')
}

/**
 * Returns a date object based on api received date.
 *
 * @param {String} apiDate
 *
 * @return {Date|false} - Returns a date object or false if apiDate is not valid
 */
function apiToDateObject(apiDate) {
  return isValidDate(apiDate, getApiFormat()) && moment(apiDate, getApiFormat()).toDate()
}

/**
 * Gets API now value.
 *
 * @param {boolean} local
 *
 * @return {string}
 */
function now(local = true) {
  return local ? moment().utc().local().format(getApiFormat()) : moment().utc().format(getApiFormat())
}

function computeElapsedTime(startDate) {
  return getTimeDiff(startDate, now(false))
}

function getTimeDiff(startDate, endDate) {
  const diff = moment(endDate).diff(moment(startDate))

  return moment.duration(diff).asSeconds()
}

export {
  getApiFormat,
  getDisplayFormat,
  isValidDate,
  apiDate,
  displayDate,
  now,
  apiToDateObject,
  dateToDisplayFormat,
  computeElapsedTime,
  getTimeDiff,
  displayDuration,
  displayTime,
  displayDateRange
}
