import React from 'react'
import {connect} from 'react-redux'
import omit from 'lodash/omit'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {API_REQUEST} from '#/main/app/api'
import {ApiRequest as ApiRequestTypes} from '#/main/app/api/prop-types'
import {Button as ButtonTypes} from '#/main/app/button/prop-types'

import {CallbackButton} from '#/main/app/button/components/callback'

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
    callback={() => props.executeRequest(props.request)}
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
  (dispatch) => ({
    executeRequest(request) {
      dispatch({
        [API_REQUEST]: request
      })
    }
  })
)(AsyncButtonComponent)

export {
  AsyncButton
}
