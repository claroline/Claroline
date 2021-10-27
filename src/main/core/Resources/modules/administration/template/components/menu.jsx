import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {MenuSection} from '#/main/app/layout/menu/components/section'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Toolbar} from '#/main/app/action'

const TemplateMenu = (props) =>
  <MenuSection
    {...omit(props, 'path')}
    title={trans('templates', {}, 'tools')}
  >
    <Toolbar
      className="list-group"
      buttonName="list-group-item"
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
          name: 'sms',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-sms',
          label: trans('sms'),
          target: `${props.path}/sms`,
          displayed: false
        }
      ]}
      onClick={props.autoClose}
    />
  </MenuSection>

TemplateMenu.propTypes = {
  path: T.string,

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired,
  autoClose: T.func.isRequired
}

export {
  TemplateMenu
}
