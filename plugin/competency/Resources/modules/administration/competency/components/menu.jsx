import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {MenuSection} from '#/main/app/layout/menu/components/section'

const CompetencyMenu = (props) =>
  <MenuSection
    {...omit(props, 'path')}
    title={trans('competencies', {}, 'tools')}
  >
    <Toolbar
      className="list-group"
      buttonName="list-group-item"
      actions={[
        {
          name: 'frameworks',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-atom',
          label: trans('competencies', {}, 'tools'),
          target: props.path + '/frameworks'
        }, {
          name: 'scales',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-arrow-up',
          label: trans('scales', {}, 'competency'),
          target: props.path + '/scales'
        }
      ]}
      onClick={props.autoClose}
    />
  </MenuSection>

CompetencyMenu.propTypes = {
  path: T.string.isRequired,

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired,
  autoClose: T.func.isRequired
}

export {
  CompetencyMenu
}
