import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {MenuSection} from '#/main/app/layout/menu/components/section'
import {Routes} from '#/main/app/router'

import {EditorMenu} from '#/plugin/claco-form/resources/claco-form/editor/components/menu'

const ClacoFormMenu = props =>
  <MenuSection
    {...omit(props, 'path')}
    title={trans('claroline_claco_form', {}, 'resource')}
  >
    <Routes
      path={props.path}
      routes={[
        {
          path: '/edit',
          disabled: !props.editable,
          render: () => (
            <EditorMenu path={props.path+'/edit'} autoClose={props.autoClose} />
          )
        }
      ]}
    />
  </MenuSection>

ClacoFormMenu.propTypes = {
  path: T.string.isRequired,
  editable: T.bool.isRequired,

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired,
  autoClose: T.func.isRequired
}

export {
  ClacoFormMenu
}
