import React from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataSearch as DataSearchTypes} from '#/main/app/data/types/prop-types'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_TEMPLATE_TYPES} from '#/main/core/modals/template-types'

// TODO : reuse explorer config (title, root, filters, etc.)

const TemplateTypeFilter = (props) =>
  <span className="data-filter template-type-filter">
    {props.search}

    <Button
      className="btn btn-filter"
      type={MODAL_BUTTON}
      tooltip="left"
      icon="fa fa-fw fa-file-alt"
      label={props.placeholder || trans('select', {}, 'actions')}
      size="sm"
      modal={[MODAL_TEMPLATE_TYPES, {
        selectAction: (selected) => ({
          type: CALLBACK_BUTTON,
          label: trans('select', {}, 'actions'),
          callback: () => props.updateSearch(selected[0].id)
        })
      }]}
      disabled={props.disabled}
    />
  </span>

implementPropTypes(TemplateTypeFilter, DataSearchTypes, {
  /*search: T.shape({
   id: T.string.isRequired,
   name: T.string.isRequired
   }),*/
  search: T.string
})

export {
  TemplateTypeFilter
}
