import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {MODAL_HEADER_SEARCH} from '#/main/core/header/search/modals/search'

import {selectors as securitySelectors} from '#/main/app/security/store'

const SearchMenuComponent = (props) => {
  if (!props.isAuthenticated) {
    return null
  }

  return (
    <Button
      id="app-search"
      type={MODAL_BUTTON}
      className="app-header-btn app-header-item"
      icon="fa fa-fw fa-search"
      label={trans('search')}
      tooltip="bottom"
      modal={[MODAL_HEADER_SEARCH]}
    />
  )
}

SearchMenuComponent.propTypes = {
  isAuthenticated: T.bool.isRequired
}

const SearchMenu = connect(
  (state) => ({
    isAuthenticated: securitySelectors.isAuthenticated(state)
  })
)(SearchMenuComponent)

export {
  SearchMenu
}
