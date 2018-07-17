import React from 'react'
import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'

import {trans} from '#/main/core/translation'
import {makeId} from '#/main/core/scaffolding/id'
import {currentUser} from '#/main/core/user/current'
import {Button} from '#/main/app/action/components/button'

import {MODAL_TAB_CREATE} from '#/main/core/tools/home/editor/modals/creation'
import {Tab as TabTypes} from '#/main/core/tools/home/prop-types'

const EditorNav = props =>
  <nav className="tool-nav">
    {props.tabs.map((tab, tabIndex) =>
      <Button
        key={tabIndex}
        type="link"
        className="nav-tab"
        icon={tab.icon ? `fa fa-fw fa-${tab.icon}` : undefined}
        label={tab.title}
        target={`/edit/tab/${tab.id}`}
        exact={true}
      />
    )}

    <Button
      className="nav-add-tab"
      type="modal"
      icon="fa fa-fw fa-plus"
      label={trans('add_tab', {}, 'home')}
      tooltip="bottom"
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
    />
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
