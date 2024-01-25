import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'

import {Action, PromisedAction} from '#/main/app/action/prop-types'

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

    /**
     * Is the current page displayed in fullscreen ?
     *
     * @type {bool}
     */
    fullscreen: T.bool,

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
    fullscreen: false
  }
}

/**
 * The definition of an application page.
 *
 * @type {object}
 */
const PageFull = {
  propTypes: merge({}, PageSimple.propTypes, {
    showBreadcrumb: T.bool,
    showHeader: T.bool,
    showTitle: T.bool,

    disabled: T.bool,

    /**
     * The path of the page inside the application (used to build the breadcrumb).
     */
    breadcrumb: T.arrayOf(T.shape({
      label: T.string.isRequired,
      displayed: T.bool,
      target: T.oneOfType([T.string, T.array])
    })),

    /**
     * @deprecated use breadcrumb
     */
    path: T.arrayOf(T.shape({
      label: T.string.isRequired,
      displayed: T.bool,
      target: T.oneOfType([T.string, T.array])
    })),

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

    nav: T.arrayOf(T.shape({

    })),

    /**
     * A custom component to add in the page header.
     */
    header: T.node,

    /**
     * The configuration for actions rendering.
     *
     * @type {string}
     */
    toolbar: T.string,

    primaryAction: T.shape(Action.propTypes),

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
    showBreadcrumb: true,
    showHeader: true,
    showTitle: true,
    disabled: false,
    actions: []
  })
}

export {
  PageFull,
  PageSimple
}
