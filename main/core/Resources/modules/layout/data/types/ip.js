
export const IP_TYPE = 'ip'

export const ipDefinition = {
  /**
   * Parses an IP string and return an array of IP parts.
   *
   * @see format() for the inverse process
   *
   * @param {string} display
   *
   * @return {Array}
   */
  parse: (display) => {
    const parsed = ['', '', '', '']

    if (display && 0 !== display.length) {
      // extracts parts from the string
      const parts = display.split('.')

      for (let i = 0; i < 4; i++) {
        if (parts[i] || '0' === parts[i]) {
          parsed[i] = '*' !== parts[i] ? parseInt(parts[i]) : parts[i]
        }
      }
    }

    return parsed
  },

  /**
   * Formats an array of IP parts into an IP string.
   *
   * @see parse() for the inverse process.
   *
   * @param {Array} parsed
   */
  format: (parsed) => parsed.join('.'),

  /**
   * Renders an IP address.
   *
   * @param {string} raw
   *
   * @return {string}
   */
  render: (raw) => raw,

  /**
   * Validates an IP string.
   *   - it MUST contains 4 groups separated by ".".
   *   - each group MUST be a number between 0 and 255 or "*".
   *
   * @param {string} value
   *
   * @return {boolean}
   */
  validate: (value) => {
    const regex = /^([0-9]{1,3}|[\\*])\.([0-9]{1,3}|[\\*])\.([0-9]{1,3}|[\\*])\.([0-9]{1,3}|[\\*])$/g

    return typeof value === 'string' && regex.test(value)
  }
}
