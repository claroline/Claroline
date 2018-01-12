import {constants as intlConstants} from '#/main/core/intl/constants'
import {constants as clacoFormConstants} from './constants'

export const generateFieldKey = (id) => {
  return  `%field_${id}%`
}

export const getFieldType = (value) => {
  return clacoFormConstants.FIELD_TYPES.find(f => f.value === value)
}

export const getCountry = (value) => intlConstants.REGIONS[value] || null

export const getFileType = (mimeType) => {
  const typeParts = mimeType.split('/')
  let type = 'file'

  if (typeParts[0] && ['image', 'audio', 'video'].indexOf(typeParts[0]) > -1) {
    type = typeParts[0]
  } else if (typeParts[1]) {
    type = typeParts[1]
  }

  return type
}