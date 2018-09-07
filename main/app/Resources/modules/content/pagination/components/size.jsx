import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans, transChoice} from '#/main/core/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons/callback'
import {MenuButton} from '#/main/app/buttons/menu/components/button'

const PaginationSize = props =>
  <MenuButton
    id="page-sizes-dropdown"
    className="btn btn-link"
    containerClassName="results-per-page"
    menu={{
      position: 'top',
      align: 'right',
      label: trans('results_per_page'),
      items: props.availableSizes.map((size) => ({
        type: CALLBACK_BUTTON,
        label: transChoice('list_results_count', size, {count: size}, 'platform'),
        active: size === props.pageSize,
        callback: () => props.updatePageSize(size)
      }))
    }}
  >
    {-1 !== props.pageSize ? props.pageSize : trans('all')}
    <span className="fa fa-sort icon-with-text-left" />
  </MenuButton>

PaginationSize.propTypes = {
  pageSize: T.number.isRequired,
  availableSizes: T.arrayOf(T.number).isRequired,
  updatePageSize: T.func.isRequired
}

export {
  PaginationSize
}
