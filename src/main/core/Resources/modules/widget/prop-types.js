import {PropTypes as T} from 'prop-types'

const DataSource = {
  propTypes: {
    id: T.string.isRequired,
    name: T.string,
    meta: T.shape({
      context: T.arrayOf(T.string)
    }),
    tags: T.arrayOf(T.string)
  },
  defaultProps: {
    meta: {
      exportable: false
    },
    tags: []
  }
}

const Widget = {
  propTypes: {
    id: T.string.isRequired,
    name: T.string,
    meta: T.shape({
      context: T.arrayOf(T.string),
      exportable: T.bool
    }),
    sources: T.arrayOf(T.string),
    tags: T.arrayOf(T.string)
  },
  defaultProps: {
    meta: {
      exportable: false
    },
    sources: [],
    tags: []
  }
}

const WidgetInstance = {
  propTypes: {
    id: T.string.isRequired,
    type: T.string.isRequired,
    // specific parameters of the content
    // depends on the `type`
    parameters: T.object
  },
  defaultProps: {
    
  }
}

const WidgetContainer = {
  propTypes: {
    id: T.string.isRequired,
    name: T.string,
    description: T.string,
    visible : T.bool.isRequired,
    display: T.shape({
      layout: T.arrayOf(
        T.number // the ratio for each col
      ).isRequired,
      alignName:T.oneOf(['left', 'center', 'right']),
      titleColor: T.string,
      backgroundType: T.oneOf(['none', 'color', 'image']),
      background: T.oneOfType([
        T.string,
        T.object
      ]), // either the color or the image (object)
      boxShadow: T.string,
      textColor: T.string,
      maxContentWidth: T.string,
      titleLevel: T.oneOf([1, 2, 3, 4, 5, 6]),
      borderRadius: T.string
    }),
    contents: T.arrayOf(T.shape(
      WidgetInstance.propTypes
    ))
  },
  defaultProps: {
    visible: true,
    display: {
      layout: [1],
      color: null,
      backgroundType: 'none',
      background: null,
      titleLevel: 2
    },
    contents: []
  }
}

export {
  DataSource,
  Widget,
  WidgetInstance,
  WidgetContainer
}
