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
    size: T.oneOf(['xs', 'sm', 'md', 'lg']),
    orientation: T.oneOf(['col', 'row']),
    className: T.string,
    poster: T.string,
    color: T.string,
    icon: T.oneOfType([T.string, T.element]),
    asIcon: T.bool,
    name: T.string,
    title: T.node,
    contentText: T.node,
    display: T.arrayOf(T.string),
    status: T.shape({
      variant: T.oneOf(['primary', 'secondary', 'success', 'info', 'warning', 'danger']).isRequired,
      text: T.string.isRequired
    }),
    primaryAction: T.oneOfType([
      // a regular action
      T.shape(merge({}, Action.propTypes, {
        label: T.node // make label optional
      })),
      // a promise that will resolve a list of actions
      T.shape(
        PromisedAction.propTypes
      )
    ]),
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
    toolbar: T.string,

    children: T.node,
    invalidated: T.bool.isRequired,
    loaded: T.bool.isRequired
  },
  defaultProps: {
    asIcon: false,
    size: 'sm',
    orientation: 'row',
    level: 3,
    actions: [],
    toolbar: 'more',
    loaded: true,
    invalidated: false,
    display: [
      'meta',
      'description'
    ]
  }
}

export {
  DataCard
}
