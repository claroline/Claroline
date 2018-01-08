import {trans} from '#/main/core/translation'

const RESOURCE_DOMAIN = 'resource'

export function t_res(key, placeholders = {}) {
  return trans(key, placeholders, RESOURCE_DOMAIN)
}
