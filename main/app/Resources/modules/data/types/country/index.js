import {trans, tval} from '#/main/app/intl/translation'
import {chain, string} from '#/main/core/validation'

import {constants as intlConstants} from '#/main/app/intl/constants'
import {CountryInput} from '#/main/app/data/types/country/components/input'
import {CountryFilter} from '#/main/app/data/types/country/components/filter'

const dataType = {
  name: 'country',
  meta: {
    icon: 'fa fa-fw fa-globe',
    label: trans('country', {}, 'data'),
    description: trans('country_desc', {}, 'data'),
    creatable: true
  },

  /**
   * Translates country code for display.
   * @param {string|Array} raw
   */
  render: (raw) => {
    if (raw) {
      if (Array.isArray(raw)) {
        return raw.map(country => trans(`${country.toUpperCase()}`, {}, 'regions')).join(', ')
      } else {
        return trans(`${raw.toUpperCase()}`, {}, 'regions')
      }
    }

    return null
  },

  validate: (value, options) => chain(value, options, [string, (countryCodes) => {
    if (options.multiple && Array.isArray(countryCodes)) {
      if (countryCodes.find(country => !intlConstants.REGION_CODES[country])) {
        // there are at least one invalid country in the list
        return tval('This value should be a list of valid country codes.')
      }
    } else if (!intlConstants.REGION_CODES.find(code => code === countryCodes)) {
      // invalid country code
      return tval('This value should be a valid country code.')
    }
  }]),

  components: {
    input: CountryInput,
    search: CountryFilter
  }
}

export {
  dataType
}
