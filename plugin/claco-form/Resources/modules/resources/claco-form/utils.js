import {constants as intlConstants} from '#/main/core/intl/constants'

const generateFieldKey = (id) => {
  return  `%field_${id}%`
}

const getCountry = (value) => intlConstants.REGIONS[value] || null

const getFileType = (mimeType) => {
  const typeParts = mimeType.split('/')
  let type = 'file'

  if (typeParts[0] && ['image', 'audio', 'video'].indexOf(typeParts[0]) > -1) {
    type = typeParts[0]
  } else if (typeParts[1]) {
    type = typeParts[1]
  }

  return type
}

export {
  generateFieldKey,
  getCountry,
  getFileType
}