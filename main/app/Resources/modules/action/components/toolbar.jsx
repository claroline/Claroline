import React, {Component} from 'react'
import classes from 'classnames'
import omit from 'lodash/omit'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {Await} from '#/main/app/components/await'
import {Button} from '#/main/app/action/components/button'
import {
  Action as ActionTypes,
  PromisedAction as PromisedActionTypes,
  Toolbar as ToolbarTypes
} from '#/main/app/action/prop-types'

import {buildToolbar} from '#/main/app/action/utils'

/**
 * Creates a toolbar of actions.
 *
 * @param props
 * @constructor
 */
const StaticToolbar = props => {
  const toolbar = buildToolbar(props.toolbar, props.actions, props.scope)

  return (0 !== toolbar.length &&
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
            id={`${props.id}${action.id || action.name}`}
            key={action.id || action.name}
            className={classes(`${props.className}-btn`, props.buttonName)}
            tooltip={props.tooltip}
          />
        )
      ])}
    </nav>
  ) || null
}

implementPropTypes(StaticToolbar, ToolbarTypes, {
  // a regular array of actions
  actions: T.arrayOf(T.shape(
    ActionTypes.propTypes
  ))
})

class PromisedToolbar extends Component {
  constructor(props) {
    super(props)

    this.state = {
      actions: []
    }
  }

  render() {
    return (
      <Await
        for={this.props.actions}
        then={actions => this.setState({actions: actions})}
        placeholder={
          <div className={this.props.className}>
            <span className={classes(`${this.props.className}-btn`, this.props.buttonName, 'default')}>
              <span className="fa fa-fw fa-spinner fa-spin" />
            </span>
          </div>
        }
      >
        <StaticToolbar {...this.props} actions={this.state.actions} />
      </Await>
    )
  }
}

implementPropTypes(PromisedToolbar, ToolbarTypes, {
  // a promise that will resolve a list of actions
  actions: T.shape(
    PromisedActionTypes.propTypes
  )
})

const Toolbar = props => props.actions instanceof Promise ?
  <PromisedToolbar {...props} /> :
  <StaticToolbar {...props} />

implementPropTypes(Toolbar, ToolbarTypes)

export {
  Toolbar
}
