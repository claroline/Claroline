import React from 'react'

import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'
import {OrganizationCard} from '#/main/core/user/data/components/organization-card'
import {Organization as OrganizationType} from '#/main/core/user/prop-types'
import {MODAL_ORGANIZATIONS} from '#/main/core/modals/organizations'

const OrganizationButton = props =>
  <Button
    className="btn"
    style={{marginTop: 10}}
    type={MODAL_BUTTON}
    icon="fa fa-fw fa-book"
    label={trans('select_a_organization')}
    primary={true}
    modal={[MODAL_ORGANIZATIONS, {
      url: ['apiv2_organization_list'],
      title: props.title,
      selectAction: (selected) => ({
        type: CALLBACK_BUTTON,
        callback: () => props.onChange(selected[0])
      })
    }]}
  />

OrganizationButton.propTypes = {
  title: T.string,
  onChange: T.func.isRequired
}

const OrganizationInput = props => {
  if (props.value) {
    return(
      <div>
        <OrganizationCard
          data={props.value}
          size="sm"
          orientation="col"
          actions={[
            {
              name: 'delete',
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-trash-o',
              label: trans('delete', {}, 'actions'),
              dangerous: true,
              callback: () => props.onChange(null)
            }
          ]}
        />

        <OrganizationButton
          {...props.picker}
          onChange={props.onChange}
        />
      </div>
    )
  } else {
    return (
      <EmptyPlaceholder
        size="lg"
        icon="fa fa-book"
        title={trans('no_organization')}
      >
        <OrganizationButton
          {...props.picker}
          onChange={props.onChange}
        />
      </EmptyPlaceholder>
    )
  }
}

implementPropTypes(OrganizationInput, FormFieldTypes, {
  value: T.shape(OrganizationType.propTypes),
  picker: T.shape({
    title: T.string
  })
}, {
  value: null
})

export {
  OrganizationInput
}
