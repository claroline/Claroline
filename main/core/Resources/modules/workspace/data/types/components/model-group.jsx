import React, {Component} from 'react'
import pickBy from 'lodash/pickBy'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'

import {t} from '#/main/core/translation'
import {makeCancelable} from '#/main/app/api'
import {url} from '#/main/app/api'

import {FormGroup as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'

import {Select} from '#/main/core/layout/form/components/field/select.jsx'

class ModelGroup extends Component {
  constructor(props) {
    super(props)

    this.state = {
      fetched: false,
      models: []
    }

    // retrieve locales
    this.fetchModels()
  }

  fetchModels() {
    this.pending = makeCancelable(
      fetch(
        url(['apiv2_workspace_list']) + '?filters[meta.model]=true', {credentials: 'include'}
      )
        .then(response => response.json())
        .then(
          (data) => {
            this.loadModels(data.data)
            this.pending = null
          },
          () => this.pending = null
        )
    )
  }

  loadModels(models) {
    this.setState({
      fetched: true,
      models: models
    })
  }

  componentWillUnmount() {
    if (this.pending) {
      this.pending.cancel()
    }
  }

  render() {
    return (
      <FormGroup
        {...this.props}
      >
        {!this.state.fetched &&
          <div>{t('Please wait while we load models...')}</div>
        }

        {this.state.fetched &&
          <Select
            id={this.props.id}
            choices={pickBy(this.state.models.reduce((choices, model) => {
              choices[model.id] = model.name

              return choices
            }, {}), this.props.filterChoices)}
            value={this.props.value ? this.props.value.id : ''}
            onChange={(value) => {
              this.props.onChange({
                id: value
              })
            }}
          />
        }
      </FormGroup>
    )
  }
}

implementPropTypes(ModelGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.shape({
    id: T.string.isRequired,
    name: T.string.isRequired
  }),
  filterChoices: T.func
}, {
  label: t('model'),
  filterChoices: () => true
})

export {
  ModelGroup
}
