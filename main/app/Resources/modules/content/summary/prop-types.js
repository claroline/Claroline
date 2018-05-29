import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'

import {trans} from '#/main/core/translation'
import {Action} from '#/main/app/action/prop-types'

const Summary = {
  propTypes: {
    displayed: T.bool.isRequired,
    opened: T.bool,
    pinned: T.bool,
    title: T.string,
    links: T.arrayOf(T.shape(merge({}, Action.propTypes, {
      additional: T.arrayOf(T.shape(
        Action.propTypes
      )),
      // TODO : find a way to document more nesting
      children: T.arrayOf(T.shape(
        Action.propTypes
      ))
    })))
  },
  defaultProps: {
    opened: false,
    pinned: false,
    title: trans('summary')
  }
}

export {
  Summary
}
