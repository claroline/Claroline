import {PropTypes as T} from 'prop-types'

const ResourceNode = {
  propTypes: {
    id: T.string.isRequired,
    name: T.string.isRequired,
    poster: T.shape({
      url: T.string
    }),
    display: T.shape({
      fullscreen: T.bool.isRequired
    }).isRequired,
    restrictions: T.shape({
      dates: T.arrayOf(T.string),
      code: T.string,
      allowedIps: T.arrayOf(T.string)
    }).isRequired
  }
}

export {
  ResourceNode
}
