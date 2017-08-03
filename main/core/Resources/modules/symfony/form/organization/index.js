import React from 'react'
import ReactDOM from 'react-dom'
import {Provider} from 'react-redux'
import {createStore} from '#/main/core/utilities/redux'
import {reducer} from './reducer'
import {OrganizationPicker} from '#/main/core/symfony/form/organization/organization_picker.jsx'

class OrganizationField {
  constructor(initialData) {
    this.store = createStore(reducer, initialData)
  }

  render(element) {
    ReactDOM.render(
      React.createElement(
        Provider,
        {store: this.store},
        React.createElement(OrganizationPicker)
      ),
      element
    )
  }
}

const container = document.querySelector('#organization-field-container')
const organizations = JSON.parse(container.dataset.organizations)
const options = JSON.parse(container.dataset.options)
const orgaField = new OrganizationField({organizations, options})

orgaField.render(container)
