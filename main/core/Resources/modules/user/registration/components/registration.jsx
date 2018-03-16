import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {select} from '#/main/core/user/registration/selectors'
import {trans} from '#/main/core/translation'
import {generateUrl} from '#/main/core/api/router'

/**
 * @constructor
 */
const Registration = props => {
  const link = `<a href='${generateUrl('claro_workspace_subscription_url_generate_user', {workspace: props.defaultWorkspaces[0].id})}'>${trans('here')}</a>`

  return (
    <div>
      <div className="well" dangerouslySetInnerHTML={{__html: trans('register_to_workspace_account_exists', {'link': link})}}/>
      <div>
        <div>{trans('following_workspace_registration')}</div>
        <ul>
          {props.defaultWorkspaces.map(defaultWorkspace =>
            <li key={defaultWorkspace.id}> {defaultWorkspace.name} </li>
          )}
        </ul>
      </div>
    </div>
  )
}

Registration.propTypes = {
  defaultWorkspaces: T.array.isRequired
}

const ConnectedRegistration = connect(
  (state) => ({
    defaultWorkspaces: select.defaultWorkspaces(state)
  }),
  null
)(Registration)

export {
  ConnectedRegistration as Registration
}
