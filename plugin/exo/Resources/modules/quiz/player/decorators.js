import defaultsDeep from 'lodash/defaultsDeep'

import defaults from './../defaults'

export function decorateAnswer(answer) {
  return defaultsDeep(answer, defaults.answer)
}
