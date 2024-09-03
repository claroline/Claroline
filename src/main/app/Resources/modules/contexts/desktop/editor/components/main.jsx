import React from 'react'

import {ContextEditor} from '#/main/app/context/editor/containers/main'

import {DesktopEditorOverview} from '#/main/app/contexts/desktop/editor/components/overview'
import {DesktopEditorAppearance} from '#/main/app/contexts/desktop/editor/components/appearance'

const DesktopEditor = () =>
  <ContextEditor
    overviewPage={DesktopEditorOverview}
    appearancePage={DesktopEditorAppearance}
  />

export {
  DesktopEditor
}
