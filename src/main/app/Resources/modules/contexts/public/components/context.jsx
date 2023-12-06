import React from 'react'

import {implementPropTypes} from '#/main/app/prop-types'
import {AppContext as AppContextTypes} from '#/main/app/context/prop-types'

import {ContextMain} from '#/main/app/context/containers/main'

import {PublicMenu} from '#/main/app/contexts/public/containers/menu'

const PublicContext = (props) =>
  <ContextMain
    {...props}
    menu={PublicMenu}
  />

implementPropTypes(PublicContext, AppContextTypes)

export {
  PublicContext
}
