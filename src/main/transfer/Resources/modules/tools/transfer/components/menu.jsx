import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {MenuSection} from '#/main/app/layout/menu/components/section'

const TransferMenu = (props) =>
  <MenuSection
    {...omit(props, 'path')}
    title={trans('transfer', {}, 'tools')}
  >
    <Toolbar
      className="list-group"
      buttonName="list-group-item"
      actions={[
        {
          name: 'import-list',
          type: LINK_BUTTON,
          label: trans('all_imports', {}, 'transfer'),
          target: `${props.path}/import/history`
        }, {
          name: 'import',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-plus',
          label: trans('new_import', {}, 'transfer'),
          target: `${props.path}/import/new`
        }, {
          name: 'export-list',
          type: LINK_BUTTON,
          label: trans('all_exports', {}, 'transfer'),
          target: `${props.path}/export/history`
        }, {
          name: 'export',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-plus',
          label: trans('new_export', {}, 'transfer'),
          target: `${props.path}/export/new`
        }
      ]}
      onClick={props.autoClose}
    />
  </MenuSection>

TransferMenu.propTypes = {
  path: T.string,

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired,
  autoClose: T.func.isRequired
}

export {
  TransferMenu
}