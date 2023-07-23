import {render as renderDate, parse as parseDate} from '#/main/app/data/types/date/utils'

/**
 * Parses display date range into ISO 8601 date.
 *
 * @param {array} display
 * @param {object} options
 *
 * @return {string}
 */
function parse(display, options = {}) {
  let parsed = [null, null]
  if (display) {
    if (display[0]) {
      parsed[0] = parseDate(display[0], options)
    }

    if (display[1]) {
      parsed[1] = parseDate(display[1], options)
    }
  }

  return parsed
}

/**
 * Renders ISO date range into locale date.
 *
 * @param {array} raw
 * @param {object} options
 *
 * @return {string}
 */
function render(raw, options = {}) {
  let start = '-'
  if (raw[0]) {
    start = renderDate(raw[0], options)
  }

  let end = '-'
  if (raw[1]) {
    end = renderDate(raw[1], options)
  }

  return start + ' / ' + end
}

export {
  parse,
  render
}
