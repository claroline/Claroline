import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'

import {Action, PromisedAction} from '#/main/app/action/prop-types'
import {Route} from '#/main/app/router/prop-types'

const PageSection = {
  propTypes: merge({}, Route.propTypes, {
    icon: T.string,
    title: T.string,
    actions: T.arrayOf(T.shape(
      Action.propTypes
    )),
    content: T.any // todo find better typing
  })
}

const PageSimple = {
  propTypes: {
    id: T.string,
    className: T.string,
    size: T.oneOf(['sm', 'lg']),

    /**
     * Is the current page embedded into another one ?
     *
     * @type {bool}
     */
    embedded: T.bool,

    /**
     * Is the current page displayed in fullscreen ?
     *
     * @type {bool}
     */
    fullscreen: T.bool,

    showBreadcrumb: T.bool,

    /**
     * The path of the page inside the application (used to build the breadcrumb).
     */
    path: T.arrayOf(T.shape({
      label: T.string.isRequired,
      displayed: T.bool,
      target: T.oneOfType([T.string, T.array])
    })),

    /**
     * A custom component to add in the page header.
     */
    header: T.node,

    children: T.node.isRequired,

    /**
     * Custom data used for document head.
     */
    meta: T.shape({
      title: T.string,
      description: T.string,
      poster: T.string,
      type: T.string
    })
  },
  defaultProps: {
    embedded: false,
    fullscreen: false,
    showBreadcrumb: true
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
    showTitle: T.bool,

    disabled: T.bool,

    /**
     * The title of the page.
     *
     * @type {string}
     */
    title: T.string.isRequired,

    /**
     * An optional subtitle for the page.
     *
     * @type {React.node}
     */
    subtitle: T.node,

    /**
     * An optional icon for the page.
     * NB. we also use it to display a progression gauge.
     *
     * @type {string}
     */
    icon: T.oneOfType([T.string, T.element]),

    /**
     * An optional url to a poster image for the page.
     *
     * @type {string}
     */
    poster: T.string,

    /**
     * The configuration for actions rendering.
     *
     * @type {string}
     */
    toolbar: T.string,

    /**
     * The list of actions available for the current page.
     *
     * @type {Array}
     */
    actions: T.oneOfType([
      // a regular array of actions
      T.arrayOf(T.shape(
        Action.propTypes
      )),
      // a promise that will resolve a list of actions
      T.shape(
        PromisedAction.propTypes
      )
    ])
  }),
  defaultProps: merge({}, PageSimple.defaultProps, {
    showHeader: true,
    showTitle: true,
    disabled: false,
    actions: []
  })
}

export {
  PageFull,
  PageSimple,
  PageSection
}
