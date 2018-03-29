import isEmpty from 'lodash/isEmpty'
import {chain, array, ip, notBlank} from '#/main/core/validation'

import {IpGroup} from '#/main/core/layout/form/components/group/ip-group.jsx'

// TODO : implement IP v6 input

const IP_TYPE = 'ip'

const ipDefinition = {
  meta: {
    type: IP_TYPE
  },

  /**
   * Validates an IP string or IPs list.
   *   - it MUST contains 4 groups separated by ".".
   *   - each group MUST be a number between 0 and 255 or "*".
   *
   * @param {string} value
   * @param {object} options
   *
   * @return {boolean}
   */
  validate: (value, options = {}) => {
    if (options.multiple) {
      return chain(value, options, [array, (value) => {
        if (value) {
          const errors = {}

          value.map((ipValue, index) => {
            const error = chain(ipValue, {}, [notBlank, ip])
            if (error) {
              errors[index] = error
            }
          })

          if (!isEmpty(errors)) {
            return errors
          }
        }
      }])
    } else {
      return ip(value)
    }
  },

  components: {
    form: IpGroup
  }
}

export {
  IP_TYPE,
  ipDefinition
}
