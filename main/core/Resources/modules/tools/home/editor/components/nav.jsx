import React from 'react'
import {connect} from 'react-redux'
import merge from 'lodash/merge'

import {NavLink} from '#/main/app/router'
import {makeId} from '#/main/core/scaffolding/id'
import {currentUser} from '#/main/core/user/current'
import {Button} from '#/main/app/action/components/button'
import {actions as formActions} from '#/main/core/data/form/actions'

import {select as homeSelect} from '#/main/core/tools/home/selectors'
import {select as editorSelect} from '#/main/core/tools/home/editor/selectors'
import {MODAL_TAB_CREATE} from '#/main/core/tools/home/editor/modals/creation'
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
        {tab.icon &&
          <span className={`fa fa-fw fa-${tab.icon} icon-with-text-right`} />
        }
        {tab.title}
      </NavLink>
    )}
    <Button
      className="nav-add-tab"
      type="modal"
      icon="fa fa-fw fa-plus"
      label=""
      modal={[MODAL_TAB_CREATE, {
        data: merge({}, TabTypes.defaultProps, {
          id: makeId(),
          position: props.tabs.length + 1,
          type: props.context.type,
          user: props.context.type === 'desktop' ? currentUser() : null,
          workspace: props.context.type === 'workspace' ? {uuid: props.context.data.uuid} : null
        }),
        create: data => props.createTab(props.tabs.length, data)
      }]}
    >
    </Button>
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
