import {PropTypes as T} from 'prop-types'

import {Widget} from '#/main/core/widget/prop-types'

const Tab = {
  propTypes: {
    id: T.string.isRequired,
    type: T.oneOf(['workspace', 'admin_desktop', 'desktop', 'administration', 'home']),
    title: T.string.isRequired,
    longTitle: T.string,
    centerTitle: T.bool.isRequired,
    icon: T.string,
    poster: T.oneOfType([
      T.string,
      T.object
    ]),
    locked: T.bool,
    position: T.number,
    restrictions: T.shape({
      hidden: T.bool,
      roles: T.array
    }),
    widgets: T.arrayOf(T.shape(
      Widget.propTypes
    ))
  },
  defaultProps: {
    icon: null,
    poster: null,
    widgets: [],
    centerTitle: false,
    restrictions: {
      hidden: false,
      roles: []
    }
  }
}

export {
  Tab
}
