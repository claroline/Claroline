/**
 * Intl module.
 * Manages the internationalization/localization of the User Interface.
 */

import {
  getApiFormat,
  getDisplayFormat,
  isValidDate,
  apiDate,
  displayDate,
  displayDuration,
  displayTime,
  displayDateRange,
  now
} from '#/main/app/intl/date'
import {locale} from '#/main/app/intl/locale'
import {number, fileSize} from '#/main/app/intl/number'
import {currency} from '#/main/app/intl/currency'
import {trans, transChoice} from '#/main/app/intl/translation'

export {
  // dates
  getApiFormat,
  getDisplayFormat,
  isValidDate,
  apiDate,
  displayDate,
  displayDuration,
  displayTime,
  displayDateRange,
  now,
  // locale
  locale,
  // number
  number,
  fileSize,
  currency,
  // translator
  trans,
  transChoice
}
