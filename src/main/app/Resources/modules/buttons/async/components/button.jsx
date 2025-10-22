import React, {forwardRef, useState} from 'react'
import {useDispatch} from 'react-redux'
import classes from 'classnames'
import omit from 'lodash/omit'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {constants as apiConst} from '#/main/app/api/constants'
import {ApiRequest as ApiRequestTypes} from '#/main/app/api/prop-types'
import {Button as ButtonTypes} from '#/main/app/buttons/prop-types'

import {CallbackButton} from '#/main/app/buttons/callback/components/button'
import {trans} from '#/main/app/intl'

/**
 * Async button.
 * Renders a component that will trigger an async call on click.
 */
const AsyncButton = forwardRef((props, ref) => {
  const dispatch = useDispatch()

  const [loading, setLoading] = useState(false)

  return (
    <CallbackButton
      {...omit(props, 'request', 'loader', 'onClick')}
      className={classes(props.className, 'position-relative')}
      ref={ref}
      callback={(e) => {
        if (loading) {
          return false
        }

        setLoading(true)

        return dispatch({
          [apiConst.API_REQUEST]: props.request
        }).then(
          // success
          () => {
            setLoading(false)
            if (props.onClick) {
              props.onClick(e)
            }
          },
          // error
          () => setLoading(false)
        )
      }}
    >
      {loading ?
        <>
          <div className="position-absolute top-50 start-50 translate-middle" role="presentation">
            <div className="dot-elastic" aria-hidden={true} />
            <span className="visually-hidden">{trans('loading')}</span>
          </div>
          <span style={{visibility: 'hidden'}} aria-hidden={true}>
            {props.children}
          </span>
        </> :
        props.children
      }
    </CallbackButton>
  )
})

// for debug purpose, otherwise component is named after the HOC
AsyncButton.displayName = 'AsyncButton'

implementPropTypes(AsyncButton, ButtonTypes, {
  loader: T.bool,
  request: T.shape(
    ApiRequestTypes.propTypes
  ).isRequired
}, {
  loader: true
})

export {
  AsyncButton
}
