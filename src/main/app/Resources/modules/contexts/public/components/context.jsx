import React from 'react'

import {AppContext as AppContextTypes} from '#/main/app/context/prop-types'
import {ContextMain} from '#/main/app/context/containers/main'

import {PublicEditor} from '#/main/app/contexts/public/editor/components/main'

const PublicContext = (props) =>
  <ContextMain
    {...props}
    editor={PublicEditor}
  />

PublicContext.propsTypes = AppContextTypes.propTypes

export {
  PublicContext
}
