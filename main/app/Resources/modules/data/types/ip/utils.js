// TODO : implement IP v6

const V4_REGEX = /^([0-9]{1,3}|[\\*])\.([0-9]{1,3}|[\\*])\.([0-9]{1,3}|[\\*])\.([0-9]{1,3}|[\\*])$/

const IPv4 = {
  /**
   * Parses an IP string and return an array of IP parts.
   *
   * @see format() for the inverse process
   *
   * @param {string} ipString
   *
   * @return {Array}
   */
  parse: (ipString) => {
    const parsed = ['', '', '', '']

    if (ipString && 0 !== ipString.length) {
      // extracts parts from the string
      const parts = ipString.split('.')

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
   * @param {Array} ipArray
   */
  format: (ipArray) => ipArray.join('.'),

  /**
   * Checks if a string is a valid IP.
   *
   * @param {string} ip
   *
   * @return {boolean}
   */
  isValid: (ip) => V4_REGEX.test(ip)
}

export {
  IPv4
}
