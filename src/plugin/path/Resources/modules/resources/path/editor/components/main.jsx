import React, {useEffect} from 'react'
import {useDispatch, useSelector} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {actions as formActions} from '#/main/app/content/form'
import {ResourceEditor, selectors as resourceSelectors} from '#/main/core/resource'

import {selectors} from '#/plugin/path/resources/path/store'
import {EditorScenario} from '#/plugin/path/resources/path/editor/components/scenario'

 const PathEditor = () => {
   const dispatch = useDispatch()
   const path = useSelector(selectors.path)

   // load text resource in editor
   useEffect(() => {
     dispatch(formActions.load(resourceSelectors.EDITOR_NAME, {resource: path}))
   }, [path.id])

   return (
     <ResourceEditor
       defaultPage="scenario"
       pages={[
         {
           name: 'scenario',
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
