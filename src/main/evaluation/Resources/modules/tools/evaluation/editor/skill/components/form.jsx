import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch, useSelector} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {EditorPage} from '#/main/app/editor'

import {actions as editorActions, selectors as editorSelectors} from '#/main/core/tool/editor'

const EditorSkillForm = props => {
  const editorPath = useSelector(editorSelectors.path)

  const dispatch = useDispatch()
  const update = (steps) => dispatch(editorActions.updateResource(steps, 'steps'))

  const skillsFramework = {
    name: 'test'
  }

  return (
    <EditorPage
      title={skillsFramework.title || trans('skills_framework', {}, 'evaluation')}
      dataPart="skill"
      actions={[
        {
          name: 'summary',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-list',
          label: trans('open-summary', {}, 'actions'),
          target: editorPath+'/skills',
          exact: true
        }
      ]}
      definition={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'title',
              type: 'string',
              label: trans('title'),
              autoFocus: true,
              required: true
            }, {
              name: 'description',
              type: 'html',
              label: trans('description'),
              options: {
                //workspace: props.workspace
              }
            }
          ]
        }
      ]}
    />
  )
}

EditorSkillForm.propTypes = {

}

export {
  EditorSkillForm
}
