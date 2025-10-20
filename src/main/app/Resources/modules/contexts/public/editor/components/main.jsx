import React from 'react'

import {ContextEditor} from '#/main/app/context/editor/containers/main'

import {PublicEditorOverview} from '#/main/app/contexts/public/editor/components/overview'
import {PublicEditorAppearance} from '#/main/app/contexts/public/editor/components/appearance'

const PublicEditor = () => {
  return (
    <ContextEditor
      overviewPage={PublicEditorOverview}
      appearancePage={PublicEditorAppearance}
    />
  )
}

export {
  PublicEditor
}
