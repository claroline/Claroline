import React, {useState} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl'
import {Toolbar} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Html} from '#/main/app/components/html'

const PageBanner = ({
  content,
  type = 'primary',
  dismissible = true,
  actions = [],
  embedded = false
}) => {
  const [dismissed, setDismissed] = useState(false)

  if (dismissed) {
    return null
  }

  const closeAction = {
    name: 'close-banner',
    type: CALLBACK_BUTTON,
    icon: 'fa fa-fw fa-times',
    label: trans('hide', {}, 'actions'),
    tooltip: 'bottom',
    callback: () => setDismissed(true),
    displayed: dismissible,
    dangerous: true // this will ensure the close button is the last in the toolbar even if there are dangerous actions
  }

  let finalActions
  if (Array.isArray(actions)) {
    finalActions = [].concat(actions.map(action => Object.assign({}, action, {icon: null})), [closeAction])
  } else {
    finalActions = actions.then((loadedActions) => [].concat(loadedActions.map(action => Object.assign({}, action, {icon: null})), [closeAction]))
  }

  return (
    <div className={classes('app-banner d-flex flex-wrap align-items-center gap-2 p-2 px-3', `text-bg-${type}`, {
      'rounded-2 mb-4': embedded
    })}>
      <Html className="px-2">{content}</Html>

      <Toolbar
        className="d-flex flex-nowrap gap-2 ms-auto"
        buttonName="btn btn-link p-1 text-reset"
        actions={finalActions}
      />
    </div>
  )
}

PageBanner.propTypes = {
  content: T.string.isRequired,
  type: T.oneOf(['primary', 'info', 'warning', 'danger']),
  dismissible: T.bool,
  embedded: T.bool,
  actions: T.arrayOf(T.object)
}

export {
  PageBanner
}
