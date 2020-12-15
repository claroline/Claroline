import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'

import {File} from '#/main/core/files/prop-types'

const Text = merge({}, File, {
  propTypes: {
    content: T.string.isRequired
  }
})

export {
  Text
}
