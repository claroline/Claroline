import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

/**
 * @param {object}  addressObj
 * @param {boolean} short
 */
function getAddressString(addressObj, short = false) {
  if (isEmpty(addressObj)) {
    return null
  }

  const parts = short ? omit(addressObj, 'street1', 'street2', 'state') : addressObj

  return Object.keys(parts)
    .map((name) => addressObj[name])
    .filter(addressPart => !isEmpty(addressPart))
    .join(', ')
}

export {
  getAddressString
}
