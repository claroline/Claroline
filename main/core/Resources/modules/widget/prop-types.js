import {PropTypes as T} from 'prop-types'

const Widget = {
  propTypes: {
    id: T.string.isRequired,
    name: T.string.isRequired,
    meta: T.shape({
      abstract: T.bool,
      parent: T.object, // another Widget
      context: T.arrayOf(T.string)
    }).isRequired,
    tags: T.arrayOf(T.string)
  }
}

const WidgetInstance = {
  propTypes: {
    id: T.string.isRequired,
    type: T.string.isRequired,
    name: T.string,
    display: T.shape({
      color: T.string,
      backgroundType: T.oneOf(['none', 'color', 'image']),
      background: T.string // either the color or the image url
    }),
    // specific parameters of the instance
    // depends on the `type`
    parameters: T.object
  }
}

export {
  Widget,
  WidgetInstance
}
