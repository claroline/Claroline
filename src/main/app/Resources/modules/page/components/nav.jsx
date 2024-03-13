import React from 'react'
import {useSelector, useDispatch} from 'react-redux'

import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {actions as contextActions, selectors as contextSelectors} from '#/main/app/context/store'

const PageNav = (props) => {
  const dispatch = useDispatch()
  const menuOpened = useSelector(contextSelectors.menuOpened)

  return (
    <div className="mx-4 my-3 d-flex gap-4 align-items-center">
      <Button
        type={CALLBACK_BUTTON}
        className="app-menu-toggle position-relative"
        label={trans('menu')}
        icon={menuOpened ? 'fa fa-fw fa-chevron-left' : 'fa fa-fw fa-chevron-right'}
        tooltip="bottom"
        callback={() => dispatch(contextActions.toggleMenu())}
      />

      {props.children}
    </div>
  )
}

export {
  PageNav
}
