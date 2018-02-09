import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import cloneDeep from 'lodash/cloneDeep'
import classes from 'classnames'
import Modal from 'react-bootstrap/lib/Modal'

import {trans} from '#/main/core/translation'
import {actions as listActions} from '#/main/core/data/list/actions'
import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {MODAL_DELETE_CONFIRM} from '#/main/core/layout/modal'
import {BaseModal} from '#/main/core/layout/modal/components/base.jsx'
import {TextGroup}  from '#/main/core/layout/form/components/group/text-group.jsx'

import {actions} from '#/plugin/reservation/administration/resource-type/actions'

const MODAL_RESOURCE_TYPES = 'MODAL_RESOURCE_TYPES'

const ResourceTypeForm = props =>
  <div className={classes('resource-type-form', {'resource-type-form-new': !props.resourceType.id})}>
    <TextGroup
      id={`resource-type-form-${props.resourceType.id}`}
      label={trans('name', {}, 'platform')}
      value={props.resourceType.name}
      onChange={value => props.onChange(props.resourceType.id ? props.resourceType.id : 'new', value)}
    />
    <span className="btn-group">
      <button
        className="btn btn-primary"
        disabled={!props.resourceType.name}
        onClick={() => props.onSave(props.resourceType)}
      >
        {props.resourceType.id ? trans('save', {}, 'platform') : trans('add', {}, 'platform')}
      </button>
      <button
        className="btn btn-default"
        onClick={() => props.onCancel()}
      >
        {trans('cancel', {}, 'platform')}
      </button>
    </span>
  </div>

ResourceTypeForm.propTypes = {
  resourceType: T.shape({
    id: T.string,
    name: T.string
  }).isRequired,
  onChange: T.func.isRequired,
  onSave: T.func.isRequired,
  onCancel: T.func.isRequired
}

class ResourceTypes extends Component {
  constructor(props) {
    super(props)
    const resourceTypes = {}
    props.resourceTypes.forEach(rt => resourceTypes[rt.id] = rt)
    this.state = {
      showForm: {},
      resourceTypes: resourceTypes
    }
    this.updateResourceTypes = this.updateResourceTypes.bind(this)
    this.switchForm = this.switchForm.bind(this)
    this.saveResourceType = this.saveResourceType.bind(this)
  }

  saveResourceType(resourceType) {
    this.props.saveResourceType(resourceType)
    this.switchForm(resourceType.id ? resourceType.id : 'new', false)
  }

  updateResourceTypes(id, value) {
    const resourceTypes = cloneDeep(this.state.resourceTypes)
    resourceTypes[id]['name'] = value
    this.setState({resourceTypes: resourceTypes})
  }

  switchForm(id, enabled) {
    if (id === 'new') {
      this.setState({
        resourceTypes: Object.assign({}, this.state.resourceTypes, {new: {id: null, name: ''}}),
        showForm: Object.assign({}, this.state.showForm, {new: enabled})
      })
    } else {
      const resourceType = this.props.resourceTypes.find(rt => rt.id === id)
      this.setState({
        resourceTypes: Object.assign({}, this.state.resourceTypes, {[id]: resourceType}),
        showForm: Object.assign({}, this.state.showForm, {[id]: enabled})
      })
    }
  }

  render() {
    return (
      <BaseModal {...this.props}>
        <Modal.Header closeButton>
          <Modal.Title>
            {trans('resource_types', {}, 'reservation')}
          </Modal.Title>
        </Modal.Header>
        <Modal.Body>
          {!this.state.showForm['new'] &&
            <button
              className="btn btn-primary resource-type-creation-btn"
              onClick={() => this.switchForm('new', true)}
            >
              <span className="fa fa-fw fa-plus"/>
              {trans('add_new_resource_type', {}, 'reservation')}
            </button>
          }
          {this.state.showForm['new'] &&
            <ResourceTypeForm
              resourceType={this.state.resourceTypes['new']}
              onChange={this.updateResourceTypes}
              onSave={this.saveResourceType}
              onCancel={() => this.switchForm('new', false)}
            />
          }
          <ul className="list-group">
            {this.props.resourceTypes.map(rt =>
              <li
                className="list-group-item"
                key={`resource-type-${rt.id}`}
              >
                {this.state.showForm[rt.id] ?
                  <ResourceTypeForm
                    resourceType={this.state.resourceTypes[rt.id]}
                    onChange={this.updateResourceTypes}
                    onSave={this.saveResourceType}
                    onCancel={() => this.switchForm(rt.id, false)}
                  /> :
                  <div className="resource-type-row">
                    <a
                      href="#"
                      onClick={() => {
                        this.props.addFilter('resources', 'resourceType.name', rt.name)
                        this.props.invalidateData('resources')
                        this.props.fadeModal()
                      }}
                    >
                      {rt.name}
                    </a>
                    <span className="actions-group">
                      <span
                        className="fa fa-fw fa-pencil pointer-hand"
                        onClick={() => this.switchForm(rt.id, true)}
                      />
                      <span
                        className="fa fa-fw fa-trash pointer-hand text-danger"
                        onClick={() => this.props.showModal(MODAL_DELETE_CONFIRM, {
                          title: trans('confirm_resource_type_deletion_title', {}, 'reservation'),
                          question: trans('confirm_resource_type_deletion_content', {}, 'reservation'),
                          handleConfirm: () => this.props.deleteResourceType(rt.id)
                        })}
                      />
                    </span>
                  </div>
                }
              </li>
            )}
          </ul>
        </Modal.Body>
      </BaseModal>
    )
  }
}

ResourceTypes.propTypes = {
  resourceTypes: T.arrayOf(T.shape({
    id: T.string.isRequired,
    name: T.string.isRequired
  })),
  saveResourceType: T.func.isRequired,
  deleteResourceType: T.func.isRequired,
  showModal: T.func.isRequired,
  fadeModal: T.func.isRequired,
  addFilter: T.func.isRequired,
  invalidateData: T.func.isRequired
}

const ResourceTypesModal = connect(
  state => ({
    resourceTypes: state.resourceTypes
  }),
  dispatch => ({
    saveResourceType(resourceType) {
      dispatch(actions.saveResourceType(resourceType))
    },
    deleteResourceType(resourceTypeId) {
      dispatch(actions.deleteResourceType(resourceTypeId))
    },
    showModal(type, props) {
      dispatch(modalActions.showModal(type, props))
    },
    fadeModal() {
      dispatch(modalActions.fadeModal())
    },
    addFilter(name, property, value) {
      dispatch(listActions.addFilter(name, property, value))
    },
    invalidateData(name) {
      dispatch(listActions.invalidateData(name))
    }
  })
)(ResourceTypes)

export {
  MODAL_RESOURCE_TYPES,
  ResourceTypesModal
}
