import {PropTypes as T} from 'prop-types'

import {Role} from '#/main/core/user/prop-types'

const Tab = {
  propTypes: {
    id: T.string,
    context: T.oneOf(['workspace', 'admin_desktop', 'desktop', 'administration', 'home', 'admin']),
    type: T.string,
    title: T.string.isRequired,
    longTitle: T.string,
    slug: T.string.isRequired,
    icon: T.string,
    class: T.string.isRequired,
    poster: T.oneOfType([
      T.string,
      T.object
    ]),
    position: T.number,
    display: T.shape({
      color: T.string,
      centerTitle: T.bool
    }),
    restrictions: T.shape({
      hidden: T.bool,
      roles: T.arrayOf(T.shape(
        Role.propTypes
      ))
    }),
    parameters: T.object,
    children: T.array
  },
  defaultProps: {
    icon: null,
    poster: null,
    display: {
      centerTitle: false
    },
    restrictions: {
      hidden: false,
      roles: []
    },
    parameters: {},
    children: []
  }
}

export {
  Tab
}
