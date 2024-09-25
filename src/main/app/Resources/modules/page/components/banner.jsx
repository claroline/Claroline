import React, {useState} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl'
import {Button, Toolbar} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ContentHtml} from '#/main/app/content/components/html'

const PageBanner = (props) => {
  const [dismissed, setDismissed] = useState(false)

  if (!dismissed) {
    return (
      <div className={classes('app-banner sticky-top d-flex flex-wrap align-items-center gap-2 p-2', `text-bg-${props.type}`)}>
        <ContentHtml className="px-1">{props.content}</ContentHtml>

        {props.children}

        <Toolbar
          className="d-flex flex-nowrap gap-2 ms-auto"
          buttonName="btn btn-link p-1 text-reset"
          actions={[].concat(props.actions, [
            {
              name: 'close-banner',
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-times',
              label: trans('hide', {}, 'actions'),
              tooltip: 'bottom',
              callback: () => setDismissed(true),
              displayed: props.dismissible
            }
          ])}
        />
      </div>
    )
  }
}

PageBanner.propTypes = {
  content: T.string.isRequired,
  type: T.oneOf(['primary', 'info', 'warning', 'danger']),
  dismissible: T.bool,
  actions: T.arrayOf(T.object)
}

PageBanner.defaultProps = {
  type: 'primary',
  dismissible: true,
  actions: []
}

export {
  PageBanner
}
