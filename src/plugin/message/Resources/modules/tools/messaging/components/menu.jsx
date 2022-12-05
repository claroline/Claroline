import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {toKey} from '#/main/core/scaffolding/text'
import {Button} from '#/main/app/action/components/button'
import {LINK_BUTTON} from '#/main/app/buttons'
import {MenuSection} from '#/main/app/layout/menu/components/section'

const MessagingMenu = (props) => {
  const links = [
    {
      icon: 'fa fa-fw fa-inbox',
      label: trans('messages_received', {}, 'message'),
      path: '/received'
    }, {
      icon: 'fa fa-fw fa-paper-plane',
      label: trans('messages_sent', {}, 'message'),
      path: '/sent'
    },  {
      icon: 'fa fa-fw fa-trash',
      label: trans('messages_removed', {}, 'message'),
      path: '/deleted'
    }, {
      icon: 'fa fa-fw fa-address-book',
      label: trans('contacts', {}, 'message'),
      path: '/contacts'
    }
  ]

  return (
    <MenuSection
      {...omit(props, 'path')}
      title={trans('messaging', {}, 'tools')}
    >
      <div className="list-group">
        {links.map(link =>
          <Button
            key={toKey(link.label)}
            className="list-group-item"
            type={LINK_BUTTON}
            icon={link.icon}
            label={link.label}
            target={`${props.path}${link.path}`}
            onClick={props.autoClose}
          />
        )}
      </div>
    </MenuSection>
  )
}

MessagingMenu.propTypes = {
  path: T.string.isRequired,

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired,
  autoClose: T.func.isRequired
}

export {
  MessagingMenu
}
