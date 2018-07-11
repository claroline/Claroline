import {PropTypes as T} from 'prop-types'

import {Widget} from '#/main/core/widget/prop-types'

const Tab = {
  propTypes: {
    id: T.string.isRequired,
    title: T.string.isRequired,
    longTitle: T.string,
    centerTitle: T.bool.isRequired,
    icon: T.string,
    poster: T.shape({
      url: T.string
    }),
    position: T.number,
    type: T.oneOf(['workspace', 'admin_desktop', 'desktop']),
    widgets: T.arrayOf(T.shape(
      Widget.propTypes
    ))
  },
  defaultProps: {
    icon: null,
    poster: null,
    widgets: [],
    centerTitle: false
  }
}

export {
  Tab
}
