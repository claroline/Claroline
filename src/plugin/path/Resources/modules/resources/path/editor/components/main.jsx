import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {ResourceEditor} from '#/main/core/resource'

import {selectors} from '#/plugin/path/resources/path/store'
import {EditorScenario} from '#/plugin/path/resources/path/editor/components/scenario'
import {PathEditorAppearance} from '#/plugin/path/resources/path/editor/components/appearance'

 const PathEditor = () => {
   const path = useSelector(selectors.path)

   return (
     <ResourceEditor
       defaultPage="steps"
       additionalData={() => ({
         resource: path
       })}
       appearancePage={PathEditorAppearance}
       pages={[
         {
           name: 'steps',
           title: trans('Scenario'),
           component: EditorScenario
         }
       ]}
     />
   )
 }

export {
  PathEditor
}
