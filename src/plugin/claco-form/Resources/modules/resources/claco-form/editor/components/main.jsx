import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {ResourceEditor} from '#/main/core/resource'

import {selectors} from '#/plugin/claco-form/resources/claco-form/store'

import {EditorParameters} from '#/plugin/claco-form/resources/claco-form/editor/components/parameters'
import {EditorCategories} from '#/plugin/claco-form/resources/claco-form/editor/components/categories'
import {EditorComments} from '#/plugin/claco-form/resources/claco-form/editor/components/comments'
import {EditorKeywords} from '#/plugin/claco-form/resources/claco-form/editor/components/keywords'
import {EditorList} from '#/plugin/claco-form/resources/claco-form/editor/components/list'
import {ClacoFormEditorActions} from '#/plugin/claco-form/resources/claco-form/editor/components/actions'

const ClacoFormEditor = (props) => {
  const clacoForm = useSelector(selectors.clacoForm)
  const categories = useSelector(selectors.categories)
  const keywords = useSelector(selectors.keywords)
  const roles = useSelector(selectors.roles)

  return (
    <ResourceEditor
      additionalData={() => ({
        resource: clacoForm,
        categories: categories,
        keywords: keywords
      })}
      actionsPage={ClacoFormEditorActions}
      defaultPage="parameters"
      pages={[
        {
          name: 'parameters',
          icon: 'fa fa-fw fa-cog',
          title: trans('parameters'),
          render: () => (
            <EditorParameters
              validateTemplate={props.validateTemplate}
            />
          )
        }, {
          name: 'list',
          icon: 'fa fa-fw fa-list',
          title: trans('list'),
          component: EditorList
        }, {
          name: 'comments',
          icon: 'fa fa-fw fa-comments',
          title: trans('comments'),
          render: () => (
            <EditorComments
              roles={roles}
            />
          )
        }, {
          name: 'categories',
          icon: 'fa fa-fw fa-object-group',
          title: trans('categories'),
          component: EditorCategories
        }, {
          name: 'keywords',
          icon: 'fa fa-fw fa-font',
          title: trans('keywords'),
          component: EditorKeywords
        }
      ]}
    />
  )
}

ClacoFormEditor.propTypes = {
  validateTemplate: T.func.isRequired
}

export {
  ClacoFormEditor
}
