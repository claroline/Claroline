import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'

const Error = props =>
  <div className={'access-restriction alert alert-detailed alert-danger'}>
    <span className={classes('alert-icon')} />
    <div className="alert-content">
      {props.error}
    </div>
  </div>

Error.propTypes = {
  error: T.string.isRequired
}

const ServerErrors = props =>
  <div>
    <EmptyPlaceholder
      size="lg"
      icon="fa fa-fw fa-exclamation-circle"
      title={trans('server_error')}
      help={trans('server_error_message')}
    >
      {props.errors.map((error, i) =>
        <Error key={i} error={trans(error)}/>
      )}
    </EmptyPlaceholder>
  </div>

ServerErrors.propTypes = {
  errors: T.array.isRequired
}

export {
  ServerErrors
}
