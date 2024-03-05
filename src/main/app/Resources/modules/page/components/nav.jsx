import React from 'react'
import {useSelector, useDispatch} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {Button} from '#/main/app/action/components/button'
import {UserAvatar} from '#/main/core/user/components/avatar'
import {CallbackButton} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions as menuActions} from '#/main/app/layout/menu/store'

const PageNav = (props) => {
  const currentUser = useSelector(securitySelectors.currentUser)
  const dispatch = useDispatch()

  return (
    <div className="m-4 d-flex gap-4 align-items-center">
      <CallbackButton
        className="app-menu-toggle position-relative"
        label={trans('menu')}
        tooltip="bottom"
        callback={() => dispatch(menuActions.toggle())}
      >
        {currentUser ?
          <>
            <UserAvatar className="app-header-avatar" picture={currentUser.picture} alt={true} size="sm"/>
            <span
              className="app-header-status position-absolute top-100 start-100 translate-middle m-n1 bg-learning rounded-circle">
              <span className="visually-hidden">New alerts</span>
            </span>
          </> :
          <span className="fa fa-fw fa-bars" aria-hidden={true} />
        }
      </CallbackButton>

      {props.children}
    </div>
  )
}

PageNav.propTypes = {

}

export {
  PageNav
}
