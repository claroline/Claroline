import {PropTypes as T} from 'prop-types'

import {ListParameters} from '#/main/app/content/list/parameters/prop-types'

const Directory = {
  propTypes: {
    display: T.shape({
      showSummary: T.bool,
      openSummary: T.bool
    }),
    list: T.shape(
      ListParameters.propTypes
    )
  },
  defaultProps: {
    display: {
      showSummary: true,
      openSummary: false
    },
    list: ListParameters.defaultProps
  }
}

export {
  Directory
}
