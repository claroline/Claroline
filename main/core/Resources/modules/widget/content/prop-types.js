import {PropTypes as T} from 'prop-types'

const WidgetInstance = {
  propTypes: {
    id: T.string.isRequired,
    type: T.string.isRequired,
    source: T.string,
    // specific parameters of the content
    // depends on the `type`
    parameters: T.object
  },
  defaultProps: {
    parameters: {}
  }
}

export {
  WidgetInstance
}
