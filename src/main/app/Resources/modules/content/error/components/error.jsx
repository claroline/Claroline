import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {Html} from '#/main/app/components/html'
import {Button} from '#/main/app/action'

/**
 * A generic error component to display when a content is not accessible by the user.
 * NB. See specific error components, there may be already one for your use case.
 */
const ContentError = ({className, title, description, help, primaryAction, secondaryAction, backAction, children}) => {
  return (
    <div className={classes('text-center', className)}>
      <p className="h3">{title}</p>
      <Html as="p" className="lead">{description}</Html>

      {children}

      {(
        (backAction && get(backAction, 'displayed', true)) ||
        (secondaryAction && get(secondaryAction, 'displayed', true)) ||
        (primaryAction && get(primaryAction, 'displayed', true))
      ) &&
        <div className="col-8 mt-5 mx-auto d-flex flex-wrap gap-2 justify-content-center">
          {backAction && get(secondaryAction, 'displayed', true) &&
            <Button
              label={trans('back', {}, 'actions')}
              icon="fa fa-arrow-left"
              {...backAction}
              className="btn btn-link"
              size="lg"
            />
          }

          {primaryAction && get(primaryAction, 'displayed', true) &&
            <Button
              {...primaryAction}
              className="btn btn-primary btn-wave"
              size="lg"
            />
          }

          {secondaryAction && get(secondaryAction, 'displayed', true) &&
            <Button
              {...secondaryAction}
              className="btn btn-body"
              size="lg"
            />
          }
        </div>
      }

      {help &&
        <Html as="p" className="text-body-secondary col-sm-8 mb-0 mt-3 mx-auto fst-italic">{help}</Html>
      }
    </div>
  )
}

ContentError.propTypes = {
  className: T.string,
  title: T.string.isRequired,
  description: T.string.isRequired,
  help: T.string,
  primaryAction: T.object,
  secondaryAction: T.object,
  backAction: T.object,
  children: T.any
}

export {
  ContentError
}
