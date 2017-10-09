import {t, trans} from '#/main/core/translation'

const FIELD_TYPES = [
  {name: 'text', value: 1, label: t('text'), hasChoice: false, hasCascade: false, answerType: 'string'},
  {name: 'number', value: 2, label: t('number'), hasChoice: false, hasCascade: false, answerType: 'number'},
  {name: 'date', value: 3, label: t('date'), hasChoice: false, hasCascade: false, answerType: 'string'},
  {name: 'radio', value: 4, label: t('radio'), hasChoice: true, hasCascade: false, answerType: 'string'},
  {name: 'select', value: 5, label: t('select'), hasChoice: true, hasCascade: true, answerType: 'string'},
  {name: 'checkboxes', value: 6, label: t('checkboxes'), hasChoice: true, hasCascade: false, answerType: 'array'},
  {name: 'country', value: 7, label: t('country'), hasChoice: false, hasCascade: false, answerType: 'string'},
  {name: 'email', value: 8, label: t('email'), hasChoice: false, hasCascade: false, answerType: 'string'},
  {name: 'rich_text', value: 9, label: t('rich_text'), hasChoice: false, hasCascade: false, answerType: 'string'},
  {name: 'file', value: 11, label: t('file'), hasChoice: false, hasCascade: false, answerType: 'array'}
]

const FILE_TYPES = [
  {value: 'audio/*', label: trans('audio', {}, 'clacoform')},
  {value: 'image/*', label: trans('image', {}, 'clacoform')},
  {value: 'video/*', label: trans('video', {}, 'clacoform')},
  {value: 'application/pdf', label: 'PDF'}
]

export const constants = {
  FIELD_TYPES,
  FILE_TYPES
}