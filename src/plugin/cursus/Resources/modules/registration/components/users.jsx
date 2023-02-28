import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {Button} from '#/main/app/action/components/button'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConst} from '#/main/app/content/list/constants'
import {UserCard} from '#/main/community/user/components/card'

import {Session as SessionTypes} from '#/plugin/cursus/prop-types'
const RegistrationUsers = (props) =>
  <Fragment>
    <ListData
      className="component-container"
      name={props.name}
      fetch={{
        url: props.url,
        autoload: true
      }}
      delete={{
        url: props.unregisterUrl,
        label: trans('unregister', {}, 'actions'),
        displayed: () => hasPermission('register', props.session)
      }}
      definition={[
        {
          name: 'user',
          type: 'user',
          label: trans('user'),
          displayed: true
        }, {
          name: 'date',
          type: 'date',
          label: trans('registration_date', {}, 'cursus'),
          options: {time: true},
          displayed: true
        }, {
          name: 'userDisabled',
          label: trans('user_disabled', {}, 'community'),
          type: 'boolean',
          displayable: false,
          sortable: false,
          filterable: true
        }
      ].concat(props.customDefinition)}
      actions={props.actions}
      card={(cardProps) => <UserCard {...cardProps} data={cardProps.data.user} />}
      display={{
        current: listConst.DISPLAY_TILES_SM
      }}
    />

    {props.add && hasPermission('register', props.session) &&
      <Button
        className="btn btn-block btn-emphasis component-container"
        primary={true}
        {...props.add}
      />
    }
  </Fragment>

RegistrationUsers.propTypes = {
  session: T.shape(
    SessionTypes.propTypes
  ).isRequired,
  name: T.string.isRequired,
  url: T.oneOfType([T.string, T.array]).isRequired,
  unregisterUrl: T.oneOfType([T.string, T.array]).isRequired,
  customDefinition: T.arrayOf(T.shape({
    // data list prop types
  })),
  actions: T.func,
  add: T.shape({
    // action types
  })
}

RegistrationUsers.defaultProps = {
  customDefinition: []
}

export {
  RegistrationUsers
}
