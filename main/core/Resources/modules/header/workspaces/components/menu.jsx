import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {User as UserTypes} from '#/main/core/user/prop-types'
import {url} from '#/main/app/api'
import {Button} from '#/main/app/action/components/button'
import {URL_BUTTON} from '#/main/app/buttons'
import {MenuButton} from '#/main/app/buttons/menu/components/button'

const WorkspacesDropdown = props =>
  <ul className="app-workspaces dropdown-menu">
    {'home' !== props.currentContext.type &&
    <li role="presentation">
      <Button
        type={URL_BUTTON}
        icon="fa fa-fw fa-home"
        label={trans('home')}
        target={['claro_index']}
      />
    </li>
    }
    {('desktop' !== props.currentContext.type && props.user.id) &&
    <li role="presentation">
      <Button
        type={URL_BUTTON}
        icon="fa fa-fw fa-atlas"
        label={trans('desktop')}
        target={['claro_desktop_open']}
      />
    </li>
    }
    {/* personal workspace */}
    {props.personal &&
    <li role="presentation" key ={props.personal.id}>
      <Button
        type={URL_BUTTON}
        icon="fa fa-fw fa-book"
        label={props.personal.name}
        target={['claro_workspace_open', {'workspaceId': props.personal.id}]}
      />
    </li>
    }

    <li role="presentation" className="divider"/>
    {0 !== props.history.length &&
    <li role="presentation" className="dropdown-header">{trans('history')}</li>
    }
    {0 !== props.history.length && props.history.map((ws) =>
      <li role="presentation" key ={ws.id}>
        <Button
          type={URL_BUTTON}
          icon="fa fa-fw fa-book"
          label={ws.name}
          target={['claro_workspace_open', {'workspaceId': ws.id}]}
        />
      </li>
    )}

    {0 !== props.history.length && !props.user.id &&
    <li role="presentation" className="divider"/>
    }
    {props.user.id &&
    <li role="presentation" className="divider"/>
    }

    {/* user workspaces */}
    {props.user.id &&
    <li role="presentation">
      <Button
        type={URL_BUTTON}
        icon="fa fa-fw fa-book"
        label={trans('my_workspaces')}
        target={['claro_workspace_by_user']}
      />
    </li>
    }

    {/* public workspaces */}
    <li role="presentation">
      <Button
        type={URL_BUTTON}
        icon="fa fa-fw fa-book"
        label={trans('public_workspaces')}
        target={['claro_workspace_list']}
      />
    </li>

    {/* create new workspace */}
    {props.creatable &&
    <li role="presentation" className="divider"/>
    }
    {props.creatable &&
    <li role="presentation">
      <Button
        type={URL_BUTTON}
        primary={true}
        icon="fa fa-fw fa-plus"
        label={trans('create_workspace')}
        target={url(['claro_admin_open_tool', {'toolName': 'workspace_management'}])+'#/workspaces/new'}
      />
    </li>
    }
  </ul>

WorkspacesDropdown.propTypes = {
  currentContext: T.shape({
    type: T.oneOf(['home', 'desktop', 'administration', 'workspace']).isRequired, // TODO : use constants
    data: T.shape({
      name: T.string.isRequired
    })
  }).isRequired,
  user: T.shape(
    UserTypes.propTypes
  ).isRequired,

  personal: T.shape({
    id: T.number.isRequired,
    name: T.string
  }),
  history: T.arrayOf(T.shape({

  })),
  creatable: T.bool.isRequired
}

const WorkspacesMenu = props =>
  <MenuButton
    id="app-workspaces-menu"
    className="app-header-item app-header-btn"
    containerClassName="app-header-workspaces"
    onToggle={(isOpened) => {
      if (isOpened) {
        props.loadMenu()
      }
    }}
    menu={
      <WorkspacesDropdown
        currentContext={props.currentContext}
        user={props.user}
        personal={props.personal}
        history={props.history}
        creatable={props.creatable}
      />
    }
  >
    <div className="header-workspaces">
      <span className={classes('fa fa-fw icon-with-text-right', {
        'fa-home' : 'home' === props.currentContext.type,
        'fa-atlas': 'desktop' === props.currentContext.type,
        'fa-book' : 'workspace' === props.currentContext.type,
        'fa-cogs' : 'administration'=== props.currentContext.type
      })}/>
      {'workspace' === props.currentContext.type ? props.currentContext.data.name : trans(props.currentContext.type)}
    </div>
    <span className="fa fa-fw fa-caret-down icon-with-text-left" />
  </MenuButton>

WorkspacesMenu.propTypes = {
  currentContext: T.shape({
    type: T.oneOf(['home', 'desktop', 'administration', 'workspace']).isRequired, // TODO : use constants
    data: T.shape({
      name: T.string.isRequired
    })
  }).isRequired,
  user: T.shape(
    UserTypes.propTypes
  ).isRequired,

  personal: T.shape({
    id: T.number.isRequired,
    name: T.string
  }),
  history: T.arrayOf(T.shape({

  })),
  creatable: T.bool.isRequired,
  loadMenu: T.func.isRequired
}

export {
  WorkspacesMenu
}
