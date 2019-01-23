import React from 'react'
import isEmpty from 'lodash/isEmpty'

import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'
import {OrganizationCard} from '#/main/core/user/data/components/organization-card'
import {Organization as OrganizationType} from '#/main/core/user/prop-types'
import {MODAL_ORGANIZATIONS_PICKER} from '#/main/core/modals/organizations'

const OrganizationsButton = props =>
  <Button
    className="btn"
    style={{marginTop: 10}}
    type={MODAL_BUTTON}
    icon="fa fa-fw fa-building"
    label={trans('add_organizations')}
    primary={true}
    modal={[MODAL_ORGANIZATIONS_PICKER, {
      url: ['apiv2_organization_list'],
      title: props.title,
      selectAction: (selected) => ({
        type: CALLBACK_BUTTON,
        label: trans('select', {}, 'actions'),
        callback: () => props.onChange(selected)
      })
    }]}
  />

OrganizationsButton.propTypes = {
  title: T.string,
  onChange: T.func.isRequired
}

const OrganizationsInput = props => {
  if (!isEmpty(props.value)) {
    return(
      <div>
        {props.value.map(organization =>
          <OrganizationCard
            key={`organization-card-${organization.id}`}
            data={organization}
            actions={[
              {
                name: 'delete',
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-trash-o',
                label: trans('delete', {}, 'actions'),
                dangerous: true,
                callback: () => {
                  const newValue = props.value
                  const index = newValue.findIndex(o => o.id === organization.id)

                  if (-1 < index) {
                    newValue.splice(index, 1)
                    props.onChange(newValue)
                  }
                }
              }
            ]}
          />
        )}

        <OrganizationsButton
          {...props.picker}
          onChange={(selected) => {
            const newValue = props.value
            selected.forEach(organization => {
              const index = newValue.findIndex(o => o.id === organization.id)

              if (-1 === index) {
                newValue.push(organization)
              }
            })

            props.onChange(newValue)
          }}
        />
      </div>
    )
  } else {
    return (
      <EmptyPlaceholder
        size="lg"
        icon="fa fa-building"
        title={trans('no_organization')}
      >
        <OrganizationsButton
          {...props.picker}
          onChange={props.onChange}
        />
      </EmptyPlaceholder>
    )
  }
}

implementPropTypes(OrganizationsInput, FormFieldTypes, {
  value: T.arrayOf(T.shape(OrganizationType.propTypes)),
  picker: T.shape({
    title: T.string
  })
}, {
  value: null,
  picker: {
    title: trans('organizations_selector')
  }
})

export {
  OrganizationsInput
}
