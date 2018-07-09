import React from 'react'
import {connect} from 'react-redux'
import merge from 'lodash/merge'

import {NavLink} from '#/main/app/router'
import {makeId} from '#/main/core/scaffolding/id'
import {trans} from '#/main/core/translation'
import {currentUser} from '#/main/core/user/current'
import {ModalButton} from '#/main/app/button'
import {MODAL_DATA_FORM} from '#/main/core/data/form/modals'
import {actions as formActions} from '#/main/core/data/form/actions'

import {select as homeSelect} from '#/main/core/tools/home/selectors'
import {select as editorSelect} from '#/main/core/tools/home/editor/selectors'
import {tabFormSections} from '#/main/core/tools/home/utils'
import {Tab as TabTypes} from '#/main/core/tools/home/prop-types'


const EditorNavComponent = props =>
  <nav className="tool-nav">
    {props.tabs.map((tab, tabIndex) =>
      <NavLink
        className="nav-tab"
        key={tabIndex}
        activeClassName="nav-tab-active"
        to={`/edit/tab/${tab.id}`}
      >

        {tab.title}
      </NavLink>
    )}
    <ModalButton
      className="nav-add-tab"
      modal={[MODAL_DATA_FORM, {
        title: trans('add_tab'),
        sections: tabFormSections,
        data: merge({}, TabTypes.defaultProps, {
          id: makeId(),
          position: props.tabs.length + 1,
          type: props.context.type,
          user: props.context.type === 'desktop' ? currentUser() : null,
          workspace: props.context.type === 'workspace' ? {uuid: props.context.data.uuid} : null
        }),
        save: data => props.createTab(props.tabs.length, data)
      }]}
    >
      <span className="fa fa-plus" />
    </ModalButton>
  </nav>


const EditorNav = connect(
  (state) => ({
    tabs: editorSelect.editorData(state),
    context : homeSelect.context(state)
  }),
  dispatch => ({
    createTab(tabIndex, tab){
      dispatch(formActions.updateProp('editor', `[${tabIndex}]`, tab))
    }
  })
)(EditorNavComponent)

export {
  EditorNav
}
