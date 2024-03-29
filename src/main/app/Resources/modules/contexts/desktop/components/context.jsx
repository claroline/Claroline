import React from 'react'

import {implementPropTypes} from '#/main/app/prop-types'
import {AppContext as AppContextTypes} from '#/main/app/context/prop-types'

import {ContextMain} from '#/main/app/context/containers/main'

import {DesktopLoading} from '#/main/app/contexts/desktop/components/loading'
import {DesktopForbidden} from '#/main/app/contexts/desktop/components/forbidden'
import {DesktopMenu} from '#/main/app/contexts/desktop/containers/menu'

const DesktopContext = (props) =>
  <ContextMain
    {...props}

    parent="public"

    menu={DesktopMenu}
    loadingPage={DesktopLoading}
    forbiddenPage={DesktopForbidden}
  />

implementPropTypes(DesktopContext, AppContextTypes)

export {
  DesktopContext
}
