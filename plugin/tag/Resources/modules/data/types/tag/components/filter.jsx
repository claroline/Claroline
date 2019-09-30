import React from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataSearch as DataSearchTypes} from '#/main/app/data/types/prop-types'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_TAGS} from '#/plugin/tag/modals/tags'

const TagFilter = (props) =>
  <span className="data-filter tags-filter">
    {props.search}

    <Button
      className="btn btn-filter"
      type={MODAL_BUTTON}
      tooltip="left"
      icon="fa fa-fw fa-tags"
      label={props.placeholder || trans('select', {}, 'actions')}
      size="sm"
      modal={[MODAL_TAGS, {
        objectClass: props.objectClass,
        selectAction: (selected) => ({
          type: CALLBACK_BUTTON,
          label: trans('select', {}, 'actions'),
          callback: () => props.updateSearch(selected.map(tag => tag.id))
        })
      }]}
      disabled={props.disabled}
    />
  </span>

implementPropTypes(TagFilter, DataSearchTypes, {
  search: T.oneOfType([T.string, T.arrayOf(T.string)]),
  objectClass: T.string
})

export {
  TagFilter
}
