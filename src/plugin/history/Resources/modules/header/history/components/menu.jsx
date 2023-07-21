import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {MODAL_HISTORY} from '#/plugin/history/modals/history'

const HistoryMenu = (props) => {
  if (!props.isAuthenticated) {
    return null
  }

  return (
    <Button
      id="app-history"
      type={MODAL_BUTTON}
      className="app-header-btn app-header-item"
      icon="fa fa-fw fa-history"
      label={trans('history', {}, 'history')}
      tooltip="bottom"
      modal={[MODAL_HISTORY]}
    />
  )
}

HistoryMenu.propTypes = {
  isAuthenticated: T.bool.isRequired
}

export {
  HistoryMenu
}
