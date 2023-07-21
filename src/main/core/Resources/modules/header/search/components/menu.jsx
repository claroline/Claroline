import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {MODAL_HEADER_SEARCH} from '#/main/core/header/search/modals/search'

const SearchMenu = () =>
  <Button
    id="app-search"
    type={MODAL_BUTTON}
    className="app-header-btn app-header-item"
    icon="fa fa-fw fa-search"
    label={trans('search')}
    tooltip="bottom"
    modal={[MODAL_HEADER_SEARCH]}
  />

export {
  SearchMenu
}
