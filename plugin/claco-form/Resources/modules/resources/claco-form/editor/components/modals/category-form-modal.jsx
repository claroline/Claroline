/*global UserPicker*/
import React, {Component} from 'react'
import cloneDeep from 'lodash/cloneDeep'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import Modal from 'react-bootstrap/lib/Modal'
import classes from 'classnames'

import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {BaseModal} from '#/main/core/layout/modal/components/base.jsx'
import {CheckGroup} from '#/main/core/layout/form/components/group/check-group.jsx'
import {ColorPicker} from '#/main/core/layout/form/components/field/color-picker.jsx'
import {trans} from '#/main/core/translation'

import {Category as CategoryType} from '#/plugin/claco-form/resources/claco-form/prop-types'
import {actions} from '#/plugin/claco-form/resources/claco-form/editor/actions'

const MODAL_CATEGORY_FORM = 'MODAL_CATEGORY_FORM'

class CategoryFormModalComponent extends Component {
  constructor(props) {
    super(props)
    this.state = {
      hasError: false,
      nameError: null,
      id: props.category.id,
      name: props.category.name,
      managers: props.category.managers,
      details: {
        color: props.category.details.color !== undefined ?
          props.category.details.color :
          null,
        notify_addition: props.category.details.notify_addition !== undefined ?
          props.category.details.notify_addition :
          true,
        notify_edition: props.category.details.notify_edition !== undefined ?
          props.category.details.notify_edition :
          true,
        notify_removal: props.category.details.notify_removal !== undefined ?
          props.category.details.notify_removal :
          true,
        notify_pending_comment: props.category.details.notify_pending_comment !== undefined ?
          props.category.details.notify_pending_comment :
          true
      }
    }
    this.setManagers = this.setManagers.bind(this)
  }

  updateCategoryProps(property, value) {
    this.setState({[property]: value})
  }

  updateCategoryDetails(property, value) {
    const details = cloneDeep(this.state.details)
    details[property] = value
    this.setState({details: details})
  }

  showManagersSelection() {
    let userPicker = new UserPicker()
    const options = {
      picker_name: 'managers-picker',
      picker_title: trans('managers_selection', {}, 'clacoform'),
      multiple: true,
      return_datas: true,
      selected_users: this.getManagersIds()
    }
    userPicker.configure(options, this.setManagers)
    userPicker.open()
  }

  getManagersIds() {
    const ids = []
    this.state.managers.forEach(m => {
      if (m.autoId) {
        ids.push(m.autoId)
      } else {
        ids.push(m.id)
      }
    })

    return ids
  }

  getManagersNames() {
    const names = []
    this.state.managers.forEach(m => names.push(`${m.firstName} ${m.lastName}`))

    return names
  }

  setManagers(users) {
    console.log(users)
    const managers = users ? users : []
    this.updateCategoryProps('managers', managers)
  }

  registerCategory() {
    if (!this.state['hasError']) {
      this.props.saveCategory(this.state, this.props.isNew)
      this.props.fadeModal()
    }
  }

  validateCategory() {
    const validation = {
      hasError: false,
      nameError: null
    }

    if (!this.state['name']) {
      validation['nameError'] = trans('form_not_blank_error', {}, 'clacoform')
      validation['hasError'] = true
    }
    this.setState(validation, this.registerCategory)
  }

  render() {
    return (
      <BaseModal {...this.props}>
        <Modal.Body>
          <div className={classes('form-group form-group-align row', {'has-error': this.state.nameError})}>
            <label className="control-label col-md-3">
              {trans('name')}
            </label>
            <div className="col-md-9">
              <input
                type="text"
                className="form-control"
                value={this.state.name}
                onChange={e => this.updateCategoryProps('name', e.target.value)}
              />
              {this.state.nameError &&
                <div className="help-block field-error">
                  {this.state.nameError}
                </div>
              }
            </div>
          </div>
          <div className="form-group form-group-align row">
            <label className="control-label col-md-3">
              {trans('color')}
            </label>
            <div className="col-md-9">
              <ColorPicker
                id="category-color"
                value={this.state.details.color}
                onChange={(e) => {this.updateCategoryDetails('color', e)}}
                autoOpen={false}
              />
            </div>
          </div>
          <div className="form-group form-group-align row">
            <label className="control-label col-md-3">
              {trans('managers', {}, 'clacoform')}
            </label>
            <div className="col-md-9">
              <span className="input-group">
                <input
                  type="text"
                  className="form-control"
                  value={this.getManagersNames().join(', ')}
                  readOnly
                />
                <span className="input-group-btn">
                  <button
                    type="button"
                    className="btn btn-default"
                    onClick={() => this.showManagersSelection()}
                  >
                    <span className="fa fa-fw fa-user"></span>
                  </button>
                </span>
              </span>
            </div>
          </div>
          <hr/>
          <div>
            <u><b>{trans('notifications')} :</b></u>
          </div>
          <br/>
          <CheckGroup
            id="notify-addition"
            value={this.state.details.notify_addition}
            label={trans('addition', {}, 'clacoform')}
            onChange={checked => this.updateCategoryDetails('notify_addition', checked)}
          />
          <CheckGroup
            id="notify-edition"
            value={this.state.details.notify_edition}
            label={trans('edition', {}, 'clacoform')}
            onChange={checked => this.updateCategoryDetails('notify_edition', checked)}
          />
          <CheckGroup
            id="notify-removal"
            value={this.state.details.notify_removal}
            label={trans('removal', {}, 'clacoform')}
            onChange={checked => this.updateCategoryDetails('notify_removal', checked)}
          />
          <CheckGroup
            id="notify-pending-comment"
            value={this.state.details.notify_pending_comment}
            label={trans('comment_to_moderate', {}, 'clacoform')}
            onChange={checked => this.updateCategoryDetails('notify_pending_comment', checked)}
          />
        </Modal.Body>
        <Modal.Footer>
          <button className="btn btn-default" onClick={this.props.fadeModal}>
            {trans('cancel')}
          </button>
          <button className="btn btn-primary" onClick={() => this.validateCategory()}>
            {trans('ok')}
          </button>
        </Modal.Footer>
      </BaseModal>
    )
  }
}

CategoryFormModalComponent.propTypes = {
  category: T.shape(CategoryType.propTypes).isRequired,
  isNew: T.bool.isRequired,
  saveCategory: T.func.isRequired,
  fadeModal: T.func.isRequired
}

const CategoryFormModal = connect(
  null,
  (dispatch) => ({
    saveCategory(category, isNew) {
      dispatch(actions.saveCategory(category, isNew))
    },
    fadeModal() {
      dispatch(modalActions.fadeModal())
    }
  })
)(CategoryFormModalComponent)

export {
  MODAL_CATEGORY_FORM,
  CategoryFormModal
}
