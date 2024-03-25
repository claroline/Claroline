import React from 'react'

import {implementPropTypes} from '#/main/app/prop-types'
import {AppContext as AppContextTypes} from '#/main/app/context/prop-types'

import {ContextMain} from '#/main/app/context/containers/main'

import {AdministrationLoading} from '#/main/app/contexts/administration/components/loading'
import {AdministrationMenu} from '#/main/app/contexts/administration/containers/menu'

const AdministrationContext = (props) =>
  <ContextMain
    {...props}
    menu={AdministrationMenu}
    loadingPage={AdministrationLoading}
  />

implementPropTypes(AdministrationContext, AppContextTypes)

export {
  AdministrationContext
}
