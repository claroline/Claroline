import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolMenu} from '#/main/core/tool/containers/menu'

const TemplateMenu = (props) =>
  <ToolMenu
    actions={[
      {
        name: 'email',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-at',
        label: trans('email'),
        target: `${props.path}/email`
      }, {
        name: 'pdf',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-file-pdf',
        label: trans('pdf'),
        target: `${props.path}/pdf`
      }, {
        name: 'other',
        type: LINK_BUTTON,
        label: trans('other'),
        target: `${props.path}/other`
      }, {
        name: 'sms',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-sms',
        label: trans('sms'),
        target: `${props.path}/sms`,
        displayed: false
      }
    ]}
  />

TemplateMenu.propTypes = {
  path: T.string
}

export {
  TemplateMenu
}
