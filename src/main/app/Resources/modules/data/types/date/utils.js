import {displayDate, apiDate} from '#/main/app/intl/date'

/**
 * Parses display date into ISO 8601 date.
 *
 * @param {string} display
 * @param {object} options
 *
 * @return {string}
 */
function parse(display, options = {}) {
  return display ? apiDate(display, false, options.time) : null
}

/**
 * Renders ISO date into locale date.
 *
 * @param {string} raw
 * @param {object} options
 *
 * @return {string}
 */
function render(raw, options = {}) {
  return raw ? displayDate(raw, false, options.time) : null
}

export {
  parse,
  render
}
