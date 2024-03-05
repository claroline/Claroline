import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {Tab as TabTypes} from '#/plugin/home/prop-types'
import {MODAL_HOME_CREATION} from '#/plugin/home/tools/home/editor/modals/creation'
import {HomeTabs} from '#/plugin/home/tools/home/components/tabs'

const EditorMenu = (props) =>
  <HomeTabs
    path={props.path}
    tabs={props.tabs}
    actions={[{
      type: MODAL_BUTTON,
      icon: 'fa fa-fw fa-plus',
      label: trans('add_tab', {}, 'home'),
      modal: [MODAL_HOME_CREATION, {
        position: props.tabs.length,
        create: (tab) => {
          props.createTab(null, tab, (slug) => props.history.push(`${props.path}/edit/${slug}`))
        }
      }]
    }]}
    showHidden={true}
  />

EditorMenu.propTypes = {
  path: T.string.isRequired,
  tabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  createTab: T.func.isRequired,
  // from router
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
}

export {
  EditorMenu
}
