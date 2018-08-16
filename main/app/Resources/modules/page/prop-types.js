import {PropTypes as T} from 'prop-types'

import {Action, PromisedAction} from '#/main/app/action/prop-types'

/**
 * The definition of an application page.
 *
 * @type {object}
 */
const Page = {
  propTypes: {
    className: T.string,

    /**
     * The title of the page.
     *
     * @type {string}
     */
    title: T.string.isRequired,

    /**
     * An optional subtitle for the page.
     *
     * @type {string}
     */
    subtitle: T.string,

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
    ]),

    /**
     * The list of section available for the current page.
     *
     * @type {Array}
     */
    sections: T.arrayOf(T.shape({

    })),

    styles: T.arrayOf(T.string)
  },
  defaultProps: {
    embedded: false,
    fullscreen: false,
    actions: [],
    styles: []
  }
}

export {
  Page
}
