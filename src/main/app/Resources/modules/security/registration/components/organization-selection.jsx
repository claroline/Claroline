import React, {useEffect, useState, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors} from '#/main/app/security/registration/store/selectors'

const getOrganizationsNames = (organizations, [currentCode, ...restOfCodes]) => {
  const currentOrganization = organizations.find(organization => organization.code === currentCode)

  return currentOrganization && currentOrganization.children.length > 0 && restOfCodes.length > 0
    ? [currentOrganization.name, ...getOrganizationsNames(currentOrganization.children, restOfCodes)]
    : [currentOrganization.name]
}

const OrganizationSelection = ({getOrganizations, allOrganizations, updateMainOrganization}) => {
  useEffect(() => getOrganizations(), [])
  const [selectedCodes, setSelectedCodes] = useState([])

  const getOptions = (organizations) => organizations.reduce((acc, current) => ({
    ...acc,
    [current.code]: current.name
  }), {'': ''})

  const getFields = (currentDepthOrganizations, depth = 0) => {
    const onChange = (fieldIndex) => (event) => {
      const selectedCode = event

      setSelectedCodes((prevState) => [...prevState.slice(0, fieldIndex), selectedCode])

      const newSelectedOrganization = currentDepthOrganizations.find(org => org.code === selectedCode)

      updateMainOrganization(newSelectedOrganization.name, selectedCode)
    }

    const selectedOrganization = currentDepthOrganizations.find(org => org.code === selectedCodes[depth])

    return [
      {
        name: `_code${depth}`,
        type: 'choice',
        label: trans('organization_select_level', {level: depth + 1}),
        calculated: selectedCodes[depth],
        required: true,
        options: {
          noEmpty: true,
          condensed: true,
          choices: getOptions(currentDepthOrganizations)
        },
        onChange: onChange(depth)
      },
      ...(selectedOrganization && selectedOrganization.children.length > 0
        ? getFields(selectedOrganization.children, depth + 1)
        : [])
    ]
  }

  return allOrganizations && (
    <FormData
      level={2}
      name={selectors.FORM_NAME}
      sections={[
        {
          title: trans('general'),
          primary: true,
          component: (selectedCodes.length > 0 && (
            <Fragment>
              {trans('selected_organizations_hierarchy')}
              <ol>
                {getOrganizationsNames(allOrganizations, selectedCodes).map(name => <li key={name}>{name}</li>)}
              </ol>
            </Fragment>
          )),
          fields: getFields(allOrganizations)
        }
      ]}
    />
  )
}

OrganizationSelection.propTypes =
  {
    allOrganizations: T.array,
    getOrganizations: T.func.isRequired,
    updateMainOrganization: T.func.isRequired
  }

export {OrganizationSelection}
