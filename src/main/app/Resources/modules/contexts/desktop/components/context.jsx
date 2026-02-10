import React from 'react'

import {AppContext as AppContextTypes} from '#/main/app/context/prop-types'

import {ContextMain} from '#/main/app/context/containers/main'

import {DesktopLoading} from '#/main/app/contexts/desktop/components/loading'
import {DesktopError} from '#/main/app/contexts/desktop/components/error'
import {DesktopEditor} from '#/main/app/contexts/desktop/editor/components/main'

const DesktopContext = (props) =>
  <ContextMain
    {...props}
    editor={DesktopEditor}
    loadingPage={DesktopLoading}
    errorPage={DesktopError}
  />

DesktopContext.propTypes = AppContextTypes.propTypes

export {
  DesktopContext
}
