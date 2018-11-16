import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'

import {Action, PromisedAction} from '#/main/app/action/prop-types'

/**
 * Definition of card data.
 *
 * @type {object}
 */
const DataCard = {
  propTypes: {
    id: T.oneOfType([
      T.string, // uuid
      T.number  // autoincrement
    ]).isRequired,
    size: T.oneOf(['sm', 'lg']),
    orientation: T.oneOf(['col', 'row']),
    className: T.string,
    poster: T.string,
    color: T.string,
    icon: T.oneOfType([T.string, T.element]).isRequired,
    title: T.string.isRequired,
    subtitle: T.string,
    contentText: T.string,
    display: T.arrayOf(T.oneOf([
      'icon',
      'flags',
      'subtitle',
      'description',
      'footer'
    ])),
    flags: T.arrayOf(
      T.arrayOf(T.oneOfType([T.string, T.number]))
    ),
    primaryAction: T.shape(merge({}, Action.propTypes, {
      label: T.node // make label optional
    })),
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

    footer: T.node,
    // ATTENTION : use it will caution because it can break grid displays
    children: T.node
  },
  defaultProps: {
    size: 'sm',
    orientation: 'row',
    level: 2,
    actions: [],
    flags: [],
    display: [
      'icon',
      'flags',
      'subtitle',
      'description',
      'footer'
    ]
  }
}

export {
  DataCard
}
