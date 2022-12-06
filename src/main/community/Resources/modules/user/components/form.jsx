import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {selectors as formSelectors} from '#/main/app/content/form/store'
import {ProfileEdit} from '#/main/community/profile/containers/edit'

const UserFormComponent = (props) => {
  if (props.user) {
    return (
      <ProfileEdit
        name={props.name}
        path={props.path}
        user={props.user}
        back={props.back}
      />
    )
  }
}

UserFormComponent.propTypes = {
  path: T.string.isRequired,
  back: T.string.isRequired,
  name: T.string.isRequired,
  user: T.object
}

const UserForm = connect(
  (state, ownProps) => ({
    user: formSelectors.originalData(formSelectors.form(state, ownProps.name))
  })
)(UserFormComponent)

export {
  UserForm
}
