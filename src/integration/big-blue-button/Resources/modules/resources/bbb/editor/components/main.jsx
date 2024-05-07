import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {ResourceEditor} from '#/main/core/resource/editor'

import {selectors} from '#/integration/big-blue-button/resources/bbb/store'
import {BBBEditorAppearance} from '#/integration/big-blue-button/resources/bbb/editor/components/appearance'
import {BBBEditorParameters} from '#/integration/big-blue-button/resources/bbb/editor/components/parameters'

const BBBEditor = () => {
  const bbb = useSelector(selectors.bbb)
  
  return (
    <ResourceEditor
      additionalData={() => ({
        resource: bbb
      })}
      defaultPage="parameters"
      appearancePage={BBBEditorAppearance}
      pages={[
        {
          name: 'parameters',
          title: trans('parameters'),
          component: BBBEditorParameters
        }
      ]}
    />
  )
}

export {
  BBBEditor
}
