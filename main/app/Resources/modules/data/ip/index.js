import {trans} from '#/main/app/intl/translation'

import {ip} from '#/main/app/data/ip/validators'
import {IpGroup} from '#/main/app/data/ip/components/group'

// TODO : implement IP v6 input

const dataType = {
  name: 'ip',

  meta: {
    creatable: false,
    label: trans('ip', {}, 'data'),
    description: trans('ip_desc', {}, 'data')
  },

  /**
   * Validates an IP string.
   *   - it MUST contains 4 groups separated by ".".
   *   - each group MUST be a number between 0 and 255 or "*".
   *
   * @param {string} value
   * @param {object} options
   *
   * @return {boolean}
   */
  validate: (value, options = {}) => ip(value, options),

  components: {
    form: IpGroup
  }
}

export {
  dataType
}
