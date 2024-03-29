import React from 'react'

import {implementPropTypes} from '#/main/app/prop-types'
import {AppContext as AppContextTypes} from '#/main/app/context/prop-types'

import {ContextMain} from '#/main/app/context/containers/main'

import {AccountMenu} from '#/main/app/contexts/account/containers/menu'

const AccountContext = (props) =>
  <ContextMain
    {...props}

    parent="desktop"
    name="account"
    menu={AccountMenu}
  />

implementPropTypes(AccountContext, AppContextTypes)

export {
  AccountContext
}
