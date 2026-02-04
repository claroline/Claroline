import React, {forwardRef} from 'react'
import {useDispatch} from 'react-redux'
import omit from 'lodash/omit'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {constants as apiConst} from '#/main/app/api/constants'
import {ApiRequest as ApiRequestTypes} from '#/main/app/api/prop-types'
import {Button as ButtonTypes} from '#/main/app/buttons/prop-types'
import {AsyncButton} from '#/main/app/buttons/async/components/button'

/**
 * Request button.
 * Renders a component that will trigger an API call on click.
 */
const RequestButton = forwardRef((props, ref) => {
  const dispatch = useDispatch()

  return (
    <AsyncButton
      {...omit(props, 'request')}
      ref={ref}
      async={() => dispatch({
        [apiConst.API_REQUEST]: props.request
      })}
    >
      {props.children}
    </AsyncButton>
  )
})

// for debug purpose, otherwise component is named after the HOC
RequestButton.displayName = 'RequestButton'

implementPropTypes(RequestButton, ButtonTypes, {
  loader: T.bool,
  request: T.shape(
    ApiRequestTypes.propTypes
  ).isRequired
})

export {
  RequestButton
}
