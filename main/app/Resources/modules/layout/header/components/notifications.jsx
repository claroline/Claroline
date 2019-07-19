import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {LINK_BUTTON, MENU_BUTTON} from '#/main/app/buttons'

const NotificationsMenu = props =>
  <div className="app-header-dropdown dropdown-menu dropdown-menu-right">
    <div className="app-header-dropdown-header">
      <div className="app-header-dropdown-icon">
        <span className="fa fa-bell" />
      </div>

      <h2 className="h4">
        {trans('notifications')}
      </h2>
    </div>

    <div className="list-group">
      {props.tools.map((tool) =>
        <Button
          key={tool.name}
          className="list-group-item"
          type={LINK_BUTTON}
          icon={`fa fa-fw fa-${tool.icon}`}
          label={trans(tool.name, {}, 'tools')}
          target={`/desktop/${tool.name}`}
          subscript={0 !== props.count[tool.name] ? {
            type: 'label',
            status: 'primary',
            value: 100 > props.count[tool.name] ? props.count[tool.name] : '99+'
          } : undefined}
        />
      )}
    </div>
  </div>

NotificationsMenu.propTypes = {
  count: T.shape({
    notifications: T.number,
    messages: T.number
  }),
  tools: T.array.isRequired
}

const HeaderNotifications = props => {
  let totalCount = 0

  Object.keys(props.count).forEach(key => totalCount += props.count[key])

  return (
    <Button
      id="app-notifications-menu"
      type={MENU_BUTTON}
      className="app-header-item app-header-btn"
      icon="fa fa-fw fa-mail-bulk"
      label={trans('notifications')}
      subscript={0 !== totalCount ? {
        type: 'label',
        status: 'primary',
        value: 100 > totalCount ? totalCount : '99+'
      } : undefined}
      tooltip="bottom"
      menu={
        <NotificationsMenu
          count={props.count}
          tools={props.tools}
        />
      }
    />
  )
}


HeaderNotifications.propTypes = {
  count: T.shape({
    notifications: T.number,
    messages: T.number
  }),
  tools: T.array
}

HeaderNotifications.defaultProps = {
  tools: []
}


export {
  HeaderNotifications
}
