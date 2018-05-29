import React from 'react'
import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

import {trans} from '#/main/core/translation'

import {Button} from '#/main/app/action/components/button'
import {Action as ActionTypes} from '#/main/app/action/prop-types'

import {buildToolbar} from '#/main/app/action/utils'

/**
 * Creates a toolbar of actions.
 *
 * @param props
 * @constructor
 */
const Toolbar = props => {
  const toolbar = buildToolbar(props.toolbar, props.actions)

  return (
    <nav role="toolbar" className={props.className}>
      {toolbar.map((group, groupIndex) => [
        0 !== groupIndex &&
          <span
            key={`separator-${groupIndex}`}
            className={`${props.className}-separator`}
          />,
        ...group.map((action) =>
          <Button
            {...omit(action, 'name')}
            id={action.id || action.name}
            key={action.id || action.name}
            className={`${props.className}-btn`}
            tooltip={props.tooltip}
          />
        )
      ])}
    </nav>
  )
}

Toolbar.propTypes = {
  /**
   * The base class of the toolbar (it's used to generate classNames which can be used for styling).
   */
  className: T.string,

  /**
   * The toolbar display configuration as a string.
   */
  toolbar: T.string,
  tooltip: T.oneOf(['left', 'top', 'right', 'bottom']),
  collapsed: T.bool, // todo implement
  actions: T.arrayOf(T.shape(
    merge({}, ActionTypes.propTypes, {
      name: T.string
    })
  )).isRequired
}

Toolbar.defaultProps = {
  className: 'toolbar',
  tooltip: 'bottom',
  collapsed: false
}

export {
  Toolbar
}
