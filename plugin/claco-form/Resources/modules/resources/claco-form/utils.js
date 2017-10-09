import {constants as clacoFormConstants} from './constants'
import {constants as formConstants} from '#/main/core/layout/form/constants'

export const generateFieldKey = (id) => {
  return  `%field_${id}%`
}

export const getFieldType = (value) => {
  return clacoFormConstants.FIELD_TYPES.find(f => f.value === value)
}

export const getCountry = (value) => {
  return formConstants.COUNTRIES.find(c => c.value === value)
}

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