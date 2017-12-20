import {t} from '#/main/core/translation'

export const TYPE_DEPARTMENT = 1
export const TYPE_USER = 2
export const TYPE_TRAINING = 3

export const locationTypes = {
  [TYPE_DEPARTMENT]: t('location_type_department'),
  [TYPE_USER]: t('location_type_user'),
  [TYPE_TRAINING]: t('location_type_training')
}
