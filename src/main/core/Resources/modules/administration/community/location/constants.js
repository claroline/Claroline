import {trans} from '#/main/app/intl/translation'

export const TYPE_DEPARTMENT = 1
export const TYPE_USER = 2
export const TYPE_TRAINING = 3

export const locationTypes = {
  [TYPE_DEPARTMENT]: trans('location_type_department'),
  [TYPE_USER]: trans('location_type_user'),
  [TYPE_TRAINING]: trans('location_type_training')
}
