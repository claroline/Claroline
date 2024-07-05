import React, {useState} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ContentHtml} from '#/main/app/content/components/html'

const PageBanner = (props) => {
  const [dismissed, setDismissed] = useState(false)

  if (!dismissed) {
    return (
      <div className={classes('app-banner sticky-top d-flex align-items-center gap-2 p-2', `text-bg-${props.type}`)}>
        <ContentHtml className="ps-1 me-auto">{props.content}</ContentHtml>

        {props.children}

        {props.dismissible &&
          <Button
            className="btn btn-link p-1 text-reset"
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-times"
            label={trans('hide', {}, 'actions')}
            tooltip="bottom"
            callback={() => setDismissed(true)}
          />
        }
      </div>
    )
  }
}

PageBanner.propTypes = {
  content: T.string.isRequired,
  type: T.oneOf(['primary', 'info', 'warning', 'danger']),
  dismissible: T.bool
}

PageBanner.defaultProps = {
  type: 'primary',
  dismissible: true
}

export {
  PageBanner
}
