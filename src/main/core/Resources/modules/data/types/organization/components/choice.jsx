import React, {Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {FormFieldset} from '#/main/app/content/form/components/fieldset'

const getOrganizationsNames = (organizations, [currentId, ...restOfIds]) => {
  if (isEmpty(currentId)) {
    return []
  }

  const currentOrganization = organizations.find(organization => organization.id === currentId)

  return currentOrganization && currentOrganization.children.length > 0 && restOfIds.length > 0
    ? [currentOrganization.name, ...getOrganizationsNames(currentOrganization.children, restOfIds)]
    : [currentOrganization.name]
}

class OrganizationChoice extends Component {
  constructor(props) {
    super(props)

    this.state = {
      organizations: [],
      selected: []
    }
  }

  componentDidMount() {
    fetch(url(['apiv2_organization_list_recursive']), {
      method: 'GET' ,
      credentials: 'include'
    })
      .then(response => response.json())
      .then(response => this.setState({
        organizations: response.data,
        selected: !isEmpty(this.props.value) ? this.searchOrganization(response.data) : []
      }))
  }

  searchOrganization(organizations) {
    let tree = []

    for (let i = 0; i < organizations.length; i++) {
      if (organizations[i].id === this.props.value.id) {
        tree.push(organizations[i].id)
      } else if (!isEmpty(organizations[i].children)) {
        let subtree = this.searchOrganization(organizations[i].children)
        if (!isEmpty(subtree)) {
          tree = tree.concat([organizations[i].id], subtree)
          break
        }
      }
    }

    return tree
  }

  getFields(currentDepthOrganizations, previousDepthOrganizations = [], depth = 0) {
    let selectedOrganization = null
    if (!isEmpty(this.state.selected)) {
      selectedOrganization = currentDepthOrganizations.find(org => org.id === this.state.selected[depth])
    }

    return [
      {
        name: depth+'',
        type: 'choice',
        label: trans('organization_select_level', {level: depth + 1}),
        required: true,
        options: {
          condensed: true,
          choices: currentDepthOrganizations.reduce((acc, current) => ({
            ...acc,
            [current.id]: current.name
          }), {})
        },
        onChange: (newValue) => {
          let newSelectedOrganization = null
          if (!isEmpty(newValue)) {
            newSelectedOrganization = currentDepthOrganizations.find(org => org.id === newValue)
          } else if (!isEmpty(this.state.selected) && !isEmpty(this.state.selected[depth - 1])) {
            // select previous orga if any
            newSelectedOrganization = previousDepthOrganizations.find(org => org.id === this.state.selected[depth - 1])
          }

          this.props.onChange(newSelectedOrganization)
        }
      },
      ...(selectedOrganization && selectedOrganization.children.length > 0
        ? this.getFields(selectedOrganization.children, currentDepthOrganizations, depth + 1)
        : [])
    ]
  }

  render() {
    return (
      <FormFieldset
        id={this.props.id}
        data={this.state.selected}
        updateProp={(name, value) => this.setState({
          selected: [...this.state.selected.slice(0, parseInt(name)), value]
        })}
        disabled={this.props.disabled}
        errors={this.props.error}
        fields={!isEmpty(this.state.organizations) ? this.getFields(this.state.organizations) : []}
        setErrors={() => null}
      >
        {!isEmpty(this.state.organizations) && !isEmpty(this.state.selected.filter(value => !isEmpty(value))) &&
          <Fragment>
            {trans('selected_organizations_hierarchy')}
            <ol>
              {getOrganizationsNames(this.state.organizations, this.state.selected).map(name =>
                <li key={name}>{name}</li>
              )}
            </ol>
          </Fragment>
        }
      </FormFieldset>
    )
  }
}

OrganizationChoice.propTypes = {
  id: T.string,
  disabled: T.bool,
  value: T.object,
  error: T.oneOfType([T.string, T.object]),
  onChange: T.func.isRequired,
  onError: T.func.isRequired
}

export {
  OrganizationChoice
}
