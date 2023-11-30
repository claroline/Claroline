import { trans } from '#/main/app/intl/translation'

const CONTENT_TYPES = ['text', 'image', 'video', 'audio']

const generateInputForType = (prefix, contentType) => {
  if (contentType === 'text') {
    return {
      name: `${prefix}Content`,
      label: trans(`${prefix}_content`, {}, 'flashcard'),
      type: 'html',
      displayed: (card) => card[`${prefix}ContentType`] === 'text'
    }
  } else {
    return {
      name: `${prefix}Content`,
      type: 'file',
      label: trans('file'),
      hideLabel: true,
      displayed: (card) => card[`${prefix}ContentType`] === contentType,
      options: {
        types: [`${contentType}/*`]
      }
    }
  }
}

export const generateInputFields = (prefix) => {
  return CONTENT_TYPES.map(type => generateInputForType(prefix, type))
}
