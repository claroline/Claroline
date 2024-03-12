import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {asset} from '#/main/app/config'
import {trans} from '#/main/app/intl'
import {Toolbar, Button} from '#/main/app/action'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {UserAvatar} from '#/main/core/user/components/avatar'
import {MODAL_HISTORY} from '#/plugin/history/modals/history'

const ContextNav = (props) => {
  if (!props.currentUser) {
    return null
  }

  return (
    <aside className="app-contexts">
      <Button
        type={LINK_BUTTON}
        className="app-context-btn position-relative"
        label={trans('desktop')}
        tooltip="right"
        target="/desktop"
      >
        <UserAvatar className="" picture={props.currentUser.picture} alt={true} size="sm"/>
        <span
          className="app-context-status position-absolute top-100 start-100 translate-middle m-n1 bg-learning rounded-circle"
        >
        <span className="visually-hidden">New alerts</span>
      </span>
      </Button>

      <Button
        type={LINK_BUTTON}
        className="app-context-btn"
        icon="fa fa-fw fa-home"
        label={trans('public', {}, 'context')}
        tooltip="right"
        target="/public"
      />

      <Button
        type={LINK_BUTTON}
        className="app-context-btn"
        icon="fa fa-fw fa-sliders"
        label={trans('administration', {}, 'context')}
        tooltip="right"
        target="/administration"
      />

      <hr className="app-context-separator" />

      {props.currentContextType && 'workspace' === props.currentContextType &&
        <Button
          type={LINK_BUTTON}
          className="app-context-btn position-relative"
          label={props.currentContext.name}
          tooltip="right"
          target={props.currentContextPath}
        >
          <div
            className="app-context-icon"
            style={!isEmpty(props.currentContext.thumbnail) ? {
              backgroundImage: `url(${asset(props.currentContext.thumbnail)})`,
              backgroundSize: 'cover',
              backgroundPosition: 'center'
            } : undefined}
          />
          <span
            className="app-context-status position-absolute top-100 start-100 translate-middle m-n1 bg-learning rounded-circle"
          >
          <span className="visually-hidden">New alerts</span>
        </span>
        </Button>
      }

      <Button
        type={CALLBACK_BUTTON}
        className="app-context-btn"
        icon="fa fa-fw fa-plus"
        label={trans('Epingler un espace')}
        tooltip="right"
        callback={() => true}
      />

      <Button
        className="app-context-btn"
        tooltip="right"
        {...{
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-history',
          label: trans('history', {}, 'history'),
          modal: [MODAL_HISTORY]
        }}
      />
    </aside>
  )
}

ContextNav.propTypes = {
  currentUser: T.shape({}),
  currentContext: T.shape({}),
  currentContextType: T.string
}

export {
  ContextNav
}
