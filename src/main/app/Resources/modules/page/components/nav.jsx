import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector, useDispatch} from 'react-redux'

import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {actions as contextActions, selectors as contextSelectors} from '#/main/app/context/store'
import {PageMenu} from '#/main/app/page/components/menu'

const MenuButton = () => {
  const dispatch = useDispatch()
  const menuOpened = useSelector(contextSelectors.menuOpened)

  return (
    <Button
      type={CALLBACK_BUTTON}
      className="app-menu-toggle position-relative"
      label={trans(menuOpened ? 'hide-menu' : 'show-menu', {}, 'actions')}
      icon="fa fa-fw fa-bars"
      tooltip="bottom"
      callback={() => dispatch(contextActions.toggleMenu())}
    />
  )
}

const PageNav = (props) =>
  <div className="mx-4 my-3 d-flex gap-4 align-items-center" role="presentation">
    {!props.embedded &&
      <MenuButton />
    }

    {props.menu &&
      <PageMenu
        {...props.menu}
      />
    }
  </div>

PageNav.propTypes = {
  embedded: T.bool.isRequired,
  menu: T.shape(PageMenu.propTypes)
}

export {
  PageNav
}
