import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {ClacoForm as ClacoFormTypes} from '#/plugin/claco-form/resources/claco-form/prop-types'
import {EditorParameters} from '#/plugin/claco-form/resources/claco-form/editor/components/parameters'
import {EditorCategories} from '#/plugin/claco-form/resources/claco-form/editor/components/categories'
import {EditorComments} from '#/plugin/claco-form/resources/claco-form/editor/components/comments'
import {EditorKeywords} from '#/plugin/claco-form/resources/claco-form/editor/components/keywords'
import {EditorList} from '#/plugin/claco-form/resources/claco-form/editor/components/list'

const EditorMain = (props) =>
  <Routes
    path={props.path+'/edit'}
    redirect={[
      {from: '/', exact: true, to: '/parameters'}
    ]}
    routes={[
      {
        path: '/parameters',
        render: () => (
          <EditorParameters
            path={props.path}
            clacoForm={props.clacoForm}
            errors={props.errors}
            roles={props.roles}
            validateTemplate={props.validateTemplate}
          />
        )
      }, {
        path: '/list',
        render: () => (
          <EditorList
            path={props.path}
            clacoForm={props.clacoForm}
          />
        )
      }, {
        path: '/comments',
        render: () => (
          <EditorComments
            path={props.path}
            clacoForm={props.clacoForm}
            roles={props.roles}
          />
        )
      }, {
        path: '/categories',
        render: () => (
          <EditorCategories
            path={props.path}
            clacoForm={props.clacoForm}
            assignCategory={props.assignCategory}
            saveCategory={props.saveCategory}
            deleteCategories={props.deleteCategories}
          />
        )
      }, {
        path: '/keywords',
        render: () => (
          <EditorKeywords
            path={props.path}
            clacoForm={props.clacoForm}
            addKeyword={props.addKeyword}
            updateKeyword={props.updateKeyword}
            deleteKeywords={props.deleteKeywords}
          />
        )
      }
    ]}
  />

EditorMain.propTypes = {
  path: T.string.isRequired,
  clacoForm: T.shape(
    ClacoFormTypes.propTypes
  ),
  errors: T.object,
  roles: T.array,
  validateTemplate: T.func.isRequired,
  saveCategory: T.func.isRequired,
  addKeyword: T.func.isRequired,
  updateKeyword: T.func.isRequired,
  assignCategory: T.func.isRequired,
  deleteCategories: T.func.isRequired,
  deleteKeywords: T.func.isRequired
}

export {
  EditorMain
}
