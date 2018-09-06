import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {url} from '#/main/app/api'
import {Button} from '#/main/app/action/components/button'
import {URL_BUTTON} from '#/main/app/buttons'
import {MenuButton} from '#/main/app/buttons/menu/components/button'

const WorkspacesMenu = props =>
  <ul className="app-workspaces dropdown-menu">
    {'home' !== props.currentLocation &&
    <li role="presentation">
      <Button
        type={URL_BUTTON}
        icon="fa fa-fw fa-home"
        label={trans('home')}
        target={['claro_index']}
      />
    </li>
    }
    {'desktop' !== props.currentLocation &&
    <li role="presentation">
      <Button
        type={URL_BUTTON}
        icon="fa fa-fw fa-atlas"
        label={trans('desktop')}
        target={['claro_desktop_open']}
      />
    </li>
    }
    <li role="presentation" className="divider"/>
    {props.history &&
      <li role="presentation" className="dropdown-header">{trans('history')}</li>
    }
    {props.history &&
      props.history.map((ws) =>
        <li role="presentation" key ={ws.id}>
          <Button
            type={URL_BUTTON}
            icon="fa fa-fw fa-book"
            label={ws.name}
            target={['claro_workspace_open', {'workspaceId': ws.id}]}
          />
        </li>
      )
    }
    {props.history &&
      <li role="presentation" className="divider"/>
    }

    {/* user workspaces */}
    <li role="presentation">
      <Button
        type={URL_BUTTON}
        icon="fa fa-fw fa-book"
        label={trans('my_workspaces')}
        target={['claro_workspace_by_user']}
      />
    </li>
    <li role="presentation">
      {/* public workspaces */}
      <Button
        type={URL_BUTTON}
        icon="fa fa-fw fa-book"
        label={trans('find_workspaces')}
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
        target={url(['claro_admin_open_tool', {'toolName': 'workspace_management'}])+'#/workspaces/creation/form'}
      />
    </li>
    }
  </ul>

const HeaderWorkspaces = props =>
  <MenuButton
    id="app-workspaces-menu"
    className="app-header-item app-header-btn"
    containerClassName="app-header-workspaces"
    menu={
      <WorkspacesMenu
        personal={props.personal}
        current={props.current}
        history={props.history}
        creatable={props.creatable}
        currentLocation={props.currentLocation}
      />
    }
  >
    <div className="header-workspaces">
      <span className="fa fa-fw fa-atlas icon-with-text-right" />
      {'workspace' === props.currentLocation ? props.current.name : trans(props.currentLocation)}
    </div>
    <span className="fa fa-fw fa-caret-down icon-with-text-left" />
  </MenuButton>

HeaderWorkspaces.propTypes = {
  personal: T.shape({

  }),
  current: T.shape({
    name: T.string
  }),
  history: T.arrayOf(T.shape({

  })),
  currentLocation: T.string.isRequired,
  creatable: T.bool.isRequired
}

HeaderWorkspaces.defaultProps = {

}

export {
  HeaderWorkspaces
}
