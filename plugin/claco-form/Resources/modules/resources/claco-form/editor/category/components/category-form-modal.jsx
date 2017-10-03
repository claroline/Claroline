/*global UserPicker*/
import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import Modal from 'react-bootstrap/lib/Modal'
import classes from 'classnames'
import {BaseModal} from '#/main/core/layout/modal/components/base.jsx'
import {CheckGroup} from '#/main/core/layout/form/components/group/check-group.jsx'
import {ColorPicker} from '#/main/core/layout/form/components/field/color-picker.jsx'
import {t, trans} from '#/main/core/translation'

export const MODAL_CATEGORY_FORM = 'MODAL_CATEGORY_FORM'

export class CategoryFormModal  extends Component {
  constructor(props) {
    super(props)
    this.state = {
      hasError: false,
      nameError: null,
      id: props.category.id,
      name: props.category.name,
      color: props.category.color,
      managers: props.category.managers,
      notifyAddition: props.category.notify_addition !== undefined ? props.category.notify_addition : true,
      notifyEdition: props.category.notify_edition !== undefined ? props.category.notify_edition : true,
      notifyRemoval: props.category.notify_removal !== undefined ? props.category.notify_removal : true,
      notifyPendingComment: props.category.notify_pending_comment !== undefined ? props.category.notify_pending_comment : true
    }
    this.setManagers = this.setManagers.bind(this)
  }

  updateCategoryProps(property, value) {
    this.setState({[property]: value})
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
    this.state.managers.forEach(m => ids.push(m.id))

    return ids
  }

  getManagersNames() {
    const names = []
    this.state.managers.forEach(m => names.push(`${m.firstName} ${m.lastName}`))

    return names
  }

  setManagers(users) {
    const managers = users ? users : []
    this.updateCategoryProps('managers', managers)
  }

  registerCategory() {
    if (!this.state['hasError']) {
      this.props.confirmAction(this.state)
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
              {t('name')}
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
              {t('color')}
            </label>
            <div className="col-md-9">
              <ColorPicker
                color={this.state.color}
                onPick={(e) => {this.updateCategoryProps('color', e.hex)}}
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
                    <span className="fa fa-w fa-user"></span>
                  </button>
                </span>
              </span>
            </div>
          </div>
          <hr/>
          <div>
              <u><b>{t('notifications')} :</b></u>
          </div>
          <br/>
          <CheckGroup
            checkId="notify-addition"
            checked={this.state.notifyAddition}
            label={trans('addition', {}, 'clacoform')}
            onChange={checked => this.updateCategoryProps('notifyAddition', checked)}
          />
          <CheckGroup
            checkId="notify-edition"
            checked={this.state.notifyEdition}
            label={trans('edition', {}, 'clacoform')}
            onChange={checked => this.updateCategoryProps('notifyEdition', checked)}
          />
          <CheckGroup
            checkId="notify-removal"
            checked={this.state.notifyRemoval}
            label={trans('removal', {}, 'clacoform')}
            onChange={checked => this.updateCategoryProps('notifyRemoval', checked)}
          />
          <CheckGroup
            checkId="notify-pending-comment"
            checked={this.state.notifyPendingComment}
            label={trans('comment_to_moderate', {}, 'clacoform')}
            onChange={checked => this.updateCategoryProps('notifyPendingComment', checked)}
          />
        </Modal.Body>
        <Modal.Footer>
          <button className="btn btn-default" onClick={this.props.fadeModal}>
            {t('cancel')}
          </button>
          <button className="btn btn-primary" onClick={() => this.validateCategory()}>
            {t('ok')}
          </button>
        </Modal.Footer>
      </BaseModal>
    )
  }
}

CategoryFormModal.propTypes = {
  category: T.shape({
    id: T.number,
    name: T.string.isRequired,
    color: T.string,
    managers: T.arrayOf(T.shape({
      id: T.number.isRequired,
      firstName: T.string.isRequired,
      lastName: T.string.isRequired,
      username: T.string.isRequired,
      mail: T.string.isRequired,
      guid: T.string.isRequired
    })),
    notify_addition: T.boolean,
    notify_edition: T.boolean,
    notify_removal: T.boolean,
    notify_pending_comment: T.boolean
  }).isRequired,
  confirmAction: T.func.isRequired,
  fadeModal: T.func.isRequired
}
