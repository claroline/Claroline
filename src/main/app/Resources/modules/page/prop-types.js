import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'

const PageSimple = {
  propTypes: {
    id: T.string,
    className: T.string,

    /**
     * Is the current page embedded into another one ?
     *
     * @type {bool}
     */
    embedded: T.bool,

    children: T.node,

    /**
     * Custom data used for document head.
     */
    meta: T.shape({
      title: T.string,
      description: T.string,
      poster: T.string,
      type: T.string
    }),

    /**
     * A list of additional styles to add to the page.
     */
    styles: T.arrayOf(T.string)
  },
  defaultProps: {
    embedded: false,
    styles: []
  }
}

/**
 * The definition of an application page.
 *
 * @type {object}
 */
const PageFull = {
  propTypes: merge({}, PageSimple.propTypes, {
    showHeader: T.bool,

    disabled: T.bool,

    /**
     * The path of the page inside the application (used to build the breadcrumb).
     */
    breadcrumb: T.arrayOf(T.shape({
      label: T.string.isRequired,
      target: T.string
    })),
    root: T.bool,

    /**
     * The title of the page.
     *
     * @type {string}
     */
    title: T.string,

    /**
     * The description of the page.
     *
     * @type {string}
     */
    description: T.string,

    /**
     * An optional url to a poster image for the page.
     *
     * @type {string}
     */
    poster: T.string,
  }),
  defaultProps: merge({}, PageSimple.defaultProps, {
    showHeader: true,
    disabled: false,
    breadcrumb: [],
    root: false
  })
}

export {
  PageFull,
  PageSimple
}
