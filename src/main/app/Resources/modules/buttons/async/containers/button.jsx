import React from 'react'
import {connect} from 'react-redux'
import omit from 'lodash/omit'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {constants as apiConst} from '#/main/app/api/constants'
import {ApiRequest as ApiRequestTypes} from '#/main/app/api/prop-types'
import {Button as ButtonTypes} from '#/main/app/buttons/prop-types'

import {CallbackButton} from '#/main/app/buttons/callback/components/button'

/**
 * Async button.
 * Renders a component that will trigger an async call on click.
 *
 * NB. it requires the `api` and `alerts` reducers in your store to fully work.
 * (it can work without it, but there will be no notifications).
 *
 * @param props
 * @constructor
 */
const AsyncButtonComponent = props =>
  <CallbackButton
    {...omit(props, 'request', 'executeRequest')}
    callback={props.executeRequest}
  >
    {props.children}
  </CallbackButton>

implementPropTypes(AsyncButtonComponent, ButtonTypes, {
  request: T.shape(
    ApiRequestTypes.propTypes
  ).isRequired,

  // from redux
  executeRequest: T.func.isRequired
})

const AsyncButton = connect(
  null,
  (dispatch, ownProps) => ({
    executeRequest() {
      dispatch({
        [apiConst.API_REQUEST]: ownProps.request
      })
    }
  })
)(AsyncButtonComponent)

export {
  AsyncButton
}
