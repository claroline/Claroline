import {PropTypes as T} from 'prop-types'

import {Role} from '#/main/community/prop-types'

const Tab = {
  propTypes: {
    id: T.string,
    type: T.string,
    title: T.string.isRequired,
    longTitle: T.string,
    slug: T.string.isRequired,
    icon: T.string,
    class: T.string.isRequired,
    poster: T.string,
    position: T.number,
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
