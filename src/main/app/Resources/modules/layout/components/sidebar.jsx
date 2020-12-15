import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

const LayoutSidebar = props =>
  <aside className="app-sidebar">
    <header className="app-sidebar-header">
      <h1 className="app-sidebar-title">
        Chat
        <small>Elorfin</small>
      </h1>

      <Toolbar
        id="app-sidebar-actions"
        className="app-sidebar-actions"
        buttonName="btn-link"
        toolbar="open close more"
        tooltip="bottom"
        actions={[
          {
            name: 'open',
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-external-link-alt',
            label: trans('open', {}, 'actions'),
            callback: () => true
          }, {
            name: 'close',
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-times',
            label: trans('close', 'actions'),
            callback: props.close
          }
        ]}
      />
    </header>
  </aside>

LayoutSidebar.propTypes = {
  close: T.func.isRequired
}

export {
  LayoutSidebar
}
