import {isValidDate, localeDate, serverDate} from '#/main/core/layout/data/types/date/utils'
import {DateSearch} from '#/main/core/layout/data/types/date/components/search.jsx'

export const DATE_TYPE = 'date'

export const dateDefinition = {
  /**
   * Parses display date into ISO 8601 date.
   *
   * @param {string} display
   *
   * @return {string}
   */
  parse: (display) => serverDate(display),

  /**
   * Renders ISO date into locale date.
   *
   * @param {string} raw
   *
   * @return {string}
   */
  render: (raw) => localeDate(raw),

  /**
   * Validates input value for a date.
   *
   * @param {string} value
   *
   * @return {boolean}
   */
  validate: (value) => typeof value === 'string' && isValidDate(value),

  components: {
    form: null,
    table: null,
    search: DateSearch
  }
}
