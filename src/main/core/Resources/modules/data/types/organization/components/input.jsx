import React, {Fragment} from 'react'

import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {OrganizationCard} from '#/main/core/user/data/components/organization-card'
import {Organization as OrganizationTypes} from '#/main/core/user/prop-types'
import {MODAL_ORGANIZATIONS} from '#/main/core/modals/organizations'
import {OrganizationChoice} from '#/main/core/data/types/organization/components/choice'

const OrganizationButton = props =>
  <Button
    className="btn btn-block"
    style={{marginTop: 10}}
    type={MODAL_BUTTON}
    icon="fa fa-fw fa-plus"
    label={trans('add_organization')}
    disabled={props.disabled}
    modal={[MODAL_ORGANIZATIONS, {
      url: [props.url],
      title: props.title,
      selectAction: (selected) => ({
        type: CALLBACK_BUTTON,
        callback: () => props.onChange(selected[0])
      })
    }]}
    size={props.size}
  />

OrganizationButton.propTypes = {
  title: T.string,
  onChange: T.func.isRequired,
  size: T.string,
  disabled: T.bool,
  url: T.string
}

OrganizationButton.defaultProps = {
  url: 'apiv2_organization_list'
}

const OrganizationInput = props => {
  if ('choice' === props.mode) {
    return (
      <OrganizationChoice
        {...props}
      />
    )
  }

  const url = props.mode === 'recursive' ? 'apiv2_user_list_flat' : 'apiv2_organization_list'

  if (props.value) {
    return (
      <Fragment>
        <OrganizationCard
          data={props.value}
          size="xs"
          actions={[
            {
              name: 'delete',
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-trash-o',
              label: trans('delete', {}, 'actions'),
              dangerous: true,
              disabled: props.disabled,
              callback: () => props.onChange(null)
            }
          ]}
        />

        <OrganizationButton
          {...props.picker}
          disabled={props.disabled}
          size={props.size}
          onChange={props.onChange}
          url={url}
        />
      </Fragment>
    )
  }

  return (
    <ContentPlaceholder
      icon="fa fa-building"
      title={trans('no_organization')}
      size={props.size}
    >
      <OrganizationButton
        {...props.picker}
        disabled={props.disabled}
        size={props.size}
        onChange={props.onChange}
        url={url}
      />
    </ContentPlaceholder>
  )
}

implementPropTypes(OrganizationInput, DataInputTypes, {
  value: T.shape(OrganizationTypes.propTypes),
  picker: T.shape({
    title: T.string
  }),
  mode: T.oneOf(['picker', 'choice', 'recursive'])
}, {
  value: null,
  mode: 'picker'
})

export {
  OrganizationInput
}
