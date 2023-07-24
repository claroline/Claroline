import React, {forwardRef} from 'react'
import {useDispatch} from 'react-redux'
import omit from 'lodash/omit'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {constants as apiConst} from '#/main/app/api/constants'
import {ApiRequest as ApiRequestTypes} from '#/main/app/api/prop-types'
import {Button as ButtonTypes} from '#/main/app/buttons/prop-types'

import {CallbackButton} from '#/main/app/buttons/callback/components/button'

/**
 * Async button.
 * Renders a component that will trigger an async call on click.
 */
const AsyncButton = forwardRef((props, ref) => {
  const dispatch = useDispatch()

  return (
    <CallbackButton
      {...omit(props, 'request')}
      ref={ref}
      callback={() => dispatch({
        [apiConst.API_REQUEST]: props.request
      })}
    >
      {props.children}
    </CallbackButton>
  )
})

// for debug purpose, otherwise component is named after the HOC
AsyncButton.displayName = 'AsyncButton'

implementPropTypes(AsyncButton, ButtonTypes, {
  request: T.shape(
    ApiRequestTypes.propTypes
  ).isRequired
})

export {
  AsyncButton
}
