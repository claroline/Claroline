import React, {Fragment} from 'react'
import isEmpty from 'lodash/isEmpty'

import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {OrganizationCard} from '#/main/community/organization/components/card'
import {Organization as OrganizationTypes} from '#/main/community/prop-types'
import {MODAL_ORGANIZATIONS} from '#/main/community/modals/organizations'

const OrganizationsButton = props =>
  <Button
    className="btn btn-block"
    style={{marginTop: 10}}
    type={MODAL_BUTTON}
    icon="fa fa-fw fa-plus"
    label={trans('add_organizations')}
    disabled={props.disabled}
    modal={[MODAL_ORGANIZATIONS, {
      title: props.title,
      selectAction: (selected) => ({
        type: CALLBACK_BUTTON,
        label: trans('select', {}, 'actions'),
        callback: () => props.onChange(selected)
      })
    }]}
    size={props.size}
  />

OrganizationsButton.propTypes = {
  title: T.string,
  disabled: T.bool,
  onChange: T.func.isRequired,
  size: T.string
}

const OrganizationsInput = props => {
  if (!isEmpty(props.value)) {
    return(
      <Fragment>
        {props.value.map(organization =>
          <OrganizationCard
            key={`organization-card-${organization.id}`}
            data={organization}
            size="xs"
            actions={[
              {
                name: 'delete',
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-trash',
                label: trans('delete', {}, 'actions'),
                dangerous: true,
                disabled: props.disabled,
                callback: () => {
                  const newValue = props.value
                  const index = newValue.findIndex(g => g.id === organization.id)

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
          disabled={props.disabled}
          size={props.size}
          onChange={(selected) => {
            const newValue = props.value
            selected.forEach(organization => {
              const index = newValue.findIndex(g => g.id === organization.id)

              if (-1 === index) {
                newValue.push(organization)
              }
            })
            props.onChange(newValue)
          }}
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
      <OrganizationsButton
        {...props.picker}
        size={props.size}
        disabled={props.disabled}
        onChange={props.onChange}
      />
    </ContentPlaceholder>
  )
}

implementPropTypes(OrganizationsInput, DataInputTypes, {
  value: T.arrayOf(T.shape(
    OrganizationTypes.propTypes
  )),
  picker: T.shape({
    title: T.string
  })
}, {
  value: null
})

export {
  OrganizationsInput
}
