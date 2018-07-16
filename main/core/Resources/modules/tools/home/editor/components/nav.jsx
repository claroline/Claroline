import React from 'react'
import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'

import {NavLink} from '#/main/app/router'
import {makeId} from '#/main/core/scaffolding/id'
import {currentUser} from '#/main/core/user/current'
import {Button} from '#/main/app/action/components/button'

import {MODAL_TAB_CREATE} from '#/main/core/tools/home/editor/modals/creation'
import {Tab as TabTypes} from '#/main/core/tools/home/prop-types'


const EditorNav = props =>
  <nav className="tool-nav">
    {props.tabs.map((tab, tabIndex) =>
      <NavLink
        className="nav-tab"
        exact={true}
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
        create: data => props.create(data)
      }]}
    >
    </Button>
  </nav>

EditorNav.propTypes = {
  tabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  create: T.func,
  context: T.shape({
    type: T.string.isRequired,
    data: T.shape({
      uuid: T.string.isRequired
    })
  }).isRequired
}

export {
  EditorNav
}
