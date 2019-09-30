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
          name: 'import',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-upload',
          label: trans('import'),
          target: `${props.path}/import`
        }, {
          name: 'history',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-history',
          label: trans('history'),
          target: `${props.path}/history`
        }
      ]}
    />
  </MenuSection>

TransferMenu.propTypes = {
  path: T.string
}

export {
  TransferMenu
}