import {PropTypes as T} from 'prop-types'

const Tool = {
  propTypes: {
    icon: T.string.isRequired,
    name: T.string.isRequired,
    poster: T.object,
    thumbnail: T.object,
    display: T.shape({
      order: T.number,
      showIcon: T.bool,
      fullscreen: T.bool
    }),
    restrictions: T.shape({
      hidden: T.bool
    })
  },
  defaultProps: {
    display: {
      showIcon: false,
      fullscreen: false
    },
    restrictions: {
      hidden: false
    }
  }
}

export {
  Tool
}
