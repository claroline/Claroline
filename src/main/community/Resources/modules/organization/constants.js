import {trans} from '#/main/app/intl'

const ORGANIZATION_TYPE_EXTERNAL = 'external'
const ORGANIZATION_TYPE_INTERNAL = 'internal'

const ORGANIZATION_TYPES = {
  [ORGANIZATION_TYPE_EXTERNAL]: trans('external'),
  [ORGANIZATION_TYPE_INTERNAL]: trans('internal')
}

export const constants = {
  ORGANIZATION_TYPES,
  ORGANIZATION_TYPE_INTERNAL,
  ORGANIZATION_TYPE_EXTERNAL
}
