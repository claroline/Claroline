import React, {useMemo} from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch, useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {formatListField} from '#/main/app/content/form/parameters/utils'
import {selectors as securitySelectors} from '#/main/app/security'
import {actions as listActions} from '#/main/app/content/list'

import {RegistrationUsers} from '#/plugin/cursus/registration/components/users'
import {getRegistrationActions, getRegistrationDefaultAction} from '#/plugin/cursus/session/utils'

const SessionUsers = (props) => {
  const dispatch = useDispatch()
  const currentUser = useSelector(securitySelectors.currentUser)

  const refresher = useMemo(() => ({
    add:    () => dispatch(listActions.invalidateData(props.name)),
    update: () => dispatch(listActions.invalidateData(props.name)),
    delete: () => dispatch(listActions.invalidateData(props.name))
  }), [props.path])

  let customDefinition = [].concat(props.customDefinition || [])

  if (props.confirmation) {
    customDefinition.push({
      name: 'confirmed',
      type: 'boolean',
      label: trans('confirmed'),
      displayable: true,
      displayed: true,
      filterable: true,
      sortable: true
    })
  }

  if (props.validation) {
    customDefinition.push({
      name: 'validated',
      type: 'boolean',
      label: trans('validated'),
      displayable: true,
      displayed: true,
      filterable: true,
      sortable: true
    })
  }

  if (props.registrationForm) {
    props.registrationForm.map(formSection => {
      customDefinition = customDefinition.concat(formSection.fields.map(field => formatListField(field, customDefinition, 'data')))
    })
  }

  return (
    <RegistrationUsers
      {...props}
      primaryAction={(row) => getRegistrationDefaultAction(row, refresher, props.path, currentUser)}
      actions={(rows) => getRegistrationActions(rows, refresher, props.path, currentUser)}
      customDefinition={customDefinition}
    />
  )
}

SessionUsers.propTypes = {
  path: T.string.isRequired,
  name: T.string.isRequired,
  registrationForm: T.array,
  validation: T.bool.isRequired,
  confirmation: T.bool.isRequired,
  customDefinition: T.arrayOf(T.shape({
    // data list prop types
  }))
}

export {
  SessionUsers
}
