import React from 'react'
import {useSelector} from 'react-redux'
import get from 'lodash/get'

import {Thumbnail} from '#/main/app/components/thumbnail'
import {selectors} from '#/main/app/context/editor'
import {ContextEditor} from '#/main/app/context/editor/containers/main'

import {DesktopEditorOverview} from '#/main/app/contexts/desktop/editor/components/overview'
import {DesktopEditorAppearance} from '#/main/app/contexts/desktop/editor/components/appearance'

const DesktopEditor = () => {
  const context = useSelector(selectors.contextData)

  return (
    <ContextEditor
      thumbnail={
        <Thumbnail
          thumbnail={get(context, 'thumbnail')}
          name={get(context, 'name')}
          size="md"
          loaded={!!context}
          square={true}
        />
      }
      overviewPage={DesktopEditorOverview}
      appearancePage={DesktopEditorAppearance}
    />
  )
}

export {
  DesktopEditor
}
