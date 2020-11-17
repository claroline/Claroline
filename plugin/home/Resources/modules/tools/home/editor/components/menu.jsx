import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_HOME_CREATION} from '#/plugin/home/tools/home/editor/modals/creation'
import {Tab as TabTypes} from '#/plugin/home/prop-types'

const EditorMenu = (props) => {
  if (0 < props.tabs.length || props.canEdit) {
    return (
      <div className="list-group">
        {props.tabs.map(tab =>
          <Button
            key={tab.id}
            className="list-group-item"
            type={LINK_BUTTON}
            icon={tab.icon ? `fa fa-fw fa-${tab.icon}` : undefined}
            label={tab.title}
            target={`${props.path}/edit/${tab.slug}`}
            activeStyle={{
              borderColor: get(tab, 'display.color')
            }}
            onClick={props.autoClose}
          />
        )}

        {props.canEdit &&
          <Button
            className="list-group-item"
            type={MODAL_BUTTON}
            icon="fa fa-fw fa-plus"
            label={trans('add_tab', {}, 'home')}
            modal={[MODAL_HOME_CREATION, {
              position: props.tabs.length,
              create: (tab) => {
                props.createTab(props.tabs.length, tab, (slug) => props.history.push(`${props.path}/edit/${slug}`))
              }
            }]}
          />
        }
      </div>
    )
  }

  return null
}

EditorMenu.propTypes = {
  path: T.string,
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  canEdit: T.bool.isRequired,
  tabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  currentContext: T.shape({
    type: T.string.isRequired,
    data: T.object
  }),
  administration: T.bool,
  currentUser: T.object,
  autoClose: T.func.isRequired,
  createTab: T.func.isRequired
}

EditorMenu.defaultProps = {
  tabs: []
}

export {
  EditorMenu
}
